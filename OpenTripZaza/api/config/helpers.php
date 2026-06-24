<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/database.php';

function runEndpoint(callable $callback): void
{
    try {
        $callback(database());
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        jsonError('Terjadi kesalahan saat mengakses database.', 500, ['error' => $exception->getMessage()]);
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        jsonError($exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Terjadi kesalahan pada server.', $exception instanceof InvalidArgumentException ? 422 : 500, ['error' => $exception->getMessage()]);
    }
}

function requiredFields(array $data, array $fields): void
{
    foreach ($fields as $field) {
        if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
            throw new InvalidArgumentException("Field {$field} wajib diisi.");
        }
    }
}

function boolValue(mixed $value): int
{
    return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
}

function jsonText(mixed $value): string
{
    return json_encode($value ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function decodeText(mixed $value): mixed
{
    if (!is_string($value) || $value === '') {
        return $value;
    }
    $decoded = json_decode($value, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
}

function nullableInt(mixed $value): ?int
{
    return $value === null || $value === '' ? null : (int) $value;
}

function nullableFloat(mixed $value): ?float
{
    return $value === null || $value === '' ? null : (float) $value;
}

function appNow(): DateTimeImmutable
{
    return new DateTimeImmutable('now');
}

function scheduledEndAt(string $date, mixed $endTime = null): DateTimeImmutable
{
    $time = trim((string) $endTime);
    if ($time === '') {
        $time = '23:59:59';
    } elseif (strlen($time) === 5) {
        $time .= ':00';
    }
    return new DateTimeImmutable($date . ' ' . $time);
}

function scheduleLifecycleStatus(array $schedule, ?DateTimeImmutable $now = null): string
{
    $now ??= appNow();
    $endAt = scheduledEndAt(
        (string) ($schedule['schedule_date'] ?? $schedule['date'] ?? ''),
        $schedule['end_time'] ?? $schedule['endTime'] ?? null
    );
    if (!empty($schedule['archived_at']) || $endAt->modify('+1 day') < $now) {
        return 'archived';
    }
    return $endAt <= $now ? 'completed' : 'upcoming';
}

function scheduleIsBookable(array $schedule, ?DateTimeImmutable $now = null): bool
{
    return ($schedule['status'] ?? '') === 'active'
        && scheduleLifecycleStatus($schedule, $now) === 'upcoming'
        && (int) ($schedule['quota'] ?? 0) > (int) ($schedule['booked_count'] ?? $schedule['bookedCount'] ?? 0);
}

function bookingHoldsOpenTripSlot(mixed $status): bool
{
    return in_array((string) $status, ['Menunggu Approval', 'Disetujui', 'Selesai'], true);
}

function getOpenTripReservedParticipants(PDO $pdo, int $scheduleId): int
{
    $countStatement = $pdo->prepare(
        "SELECT COALESCE(SUM(participants), 0) FROM bookings
         WHERE schedule_id = ? AND status IN ('Menunggu Approval','Disetujui','Selesai')"
    );
    $countStatement->execute([$scheduleId]);
    return (int) $countStatement->fetchColumn();
}

function syncOpenTripAvailability(PDO $pdo, int $scheduleId, int $tripId): void
{
    $lockStatement = $pdo->prepare('SELECT id FROM trip_schedules WHERE id = ? FOR UPDATE');
    $lockStatement->execute([$scheduleId]);
    if (!$lockStatement->fetch()) {
        throw new InvalidArgumentException('Jadwal trip tidak tersedia.');
    }

    $bookedCount = getOpenTripReservedParticipants($pdo, $scheduleId);

    $scheduleStatement = $pdo->prepare(
        "UPDATE trip_schedules
         SET booked_count = ?,
             status = CASE
                WHEN status = 'inactive' THEN 'inactive'
                WHEN quota <= ? THEN 'full'
                ELSE 'active'
             END
         WHERE id = ?"
    );
    $scheduleStatement->execute([$bookedCount, $bookedCount, $scheduleId]);

    $totalsStatement = $pdo->prepare(
        'SELECT COALESCE(SUM(quota),0) quota,
                COALESCE(SUM(GREATEST(quota-booked_count,0)),0) slots
         FROM trip_schedules WHERE trip_id = ?'
    );
    $totalsStatement->execute([$tripId]);
    $totals = $totalsStatement->fetch() ?: ['quota' => 0, 'slots' => 0];
    $slots = max(0, (int) $totals['slots']);

    $tripStatement = $pdo->prepare(
        "UPDATE trips
         SET quota = ?,
             slots = ?,
             status = CASE
                WHEN status IN ('Ditutup','Selesai') THEN status
                WHEN ? <= 0 THEN 'Penuh'
                ELSE 'Tersedia'
             END
         WHERE id = ?"
    );
    $tripStatement->execute([(int) $totals['quota'], $slots, $slots, $tripId]);
}

function privateTripEndAt(array $trip, array $sessions = []): ?DateTimeImmutable
{
    $endDate = trim((string) ($trip['available_end_date'] ?? $trip['availableEndDate'] ?? ''));
    if ($endDate === '') {
        return null;
    }
    $latestEndTime = '';
    foreach ($sessions as $session) {
        $endTime = trim((string) ($session['end_time'] ?? $session['endTime'] ?? ''));
        if ($endTime > $latestEndTime) {
            $latestEndTime = $endTime;
        }
    }
    return scheduledEndAt($endDate, $latestEndTime !== '' ? $latestEndTime : null);
}

function tripLastEndAt(array $trip, array $schedules, array $sessions = []): ?DateTimeImmutable
{
    if (($trip['trip_type'] ?? $trip['type'] ?? 'open') === 'private') {
        return privateTripEndAt($trip, $sessions);
    }
    $latestEndAt = null;
    foreach ($schedules as $schedule) {
        $endAt = scheduledEndAt(
            (string) ($schedule['schedule_date'] ?? $schedule['date'] ?? ''),
            $schedule['end_time'] ?? $schedule['endTime'] ?? null
        );
        if ($latestEndAt === null || $endAt > $latestEndAt) {
            $latestEndAt = $endAt;
        }
    }
    return $latestEndAt;
}

function privateTripHasBookableSlot(array $trip, array $sessions, ?DateTimeImmutable $now = null): bool
{
    $now ??= appNow();
    $startDate = trim((string) ($trip['available_start_date'] ?? $trip['availableStartDate'] ?? ''));
    $endDate = trim((string) ($trip['available_end_date'] ?? $trip['availableEndDate'] ?? ''));
    if ($endDate === '' || ($startDate !== '' && $startDate > $endDate)) {
        return false;
    }
    foreach ($sessions as $session) {
        if (($session['status'] ?? '') !== 'active') {
            continue;
        }
        if (scheduledEndAt($endDate, $session['end_time'] ?? $session['endTime'] ?? null) > $now) {
            return true;
        }
    }
    return false;
}

function tripLifecycleStatus(array $trip, array $schedules, array $sessions = [], ?DateTimeImmutable $now = null): string
{
    $now ??= appNow();
    $latestEndAt = tripLastEndAt($trip, $schedules, $sessions);
    if ($latestEndAt === null) {
        return 'active';
    }
    if ($latestEndAt->modify('+1 day') < $now) {
        return 'archived';
    }
    return $latestEndAt <= $now ? 'completed' : 'active';
}

function customerTripProfileValues(array $data, bool $required = false): array
{
    $bloodType = trim((string) ($data['bloodType'] ?? ''));
    $heightCm = nullableInt($data['heightCm'] ?? null);
    $weightKg = nullableFloat($data['weightKg'] ?? null);
    $shoeSize = nullableFloat($data['shoeSize'] ?? null);

    if ($required && ($bloodType === '' || $heightCm === null || $weightKg === null || $shoeSize === null)) {
        throw new InvalidArgumentException(
            'Lengkapi Golongan Darah, Tinggi Badan, Berat Badan, dan Ukuran Sepatu pada profil sebelum checkout.'
        );
    }
    if ($bloodType !== '' && !in_array($bloodType, ['A', 'B', 'AB', 'O', 'Tidak tahu'], true)) {
        throw new InvalidArgumentException('Golongan darah tidak valid.');
    }
    if ($heightCm !== null && ($heightCm < 50 || $heightCm > 250)) {
        throw new InvalidArgumentException('Tinggi badan harus antara 50 sampai 250 cm.');
    }
    if ($weightKg !== null && ($weightKg < 20 || $weightKg > 300)) {
        throw new InvalidArgumentException('Berat badan harus antara 20 sampai 300 kg.');
    }
    if ($shoeSize !== null && ($shoeSize < 20 || $shoeSize > 55)) {
        throw new InvalidArgumentException('Ukuran sepatu harus antara 20 sampai 55.');
    }

    return [
        $bloodType === '' ? null : $bloodType,
        $heightCm,
        $weightKg,
        $shoeSize,
    ];
}

function publicUploadPath(string $folder, string $filename): string
{
    $path = '/uploads/' . trim($folder, '/') . '/' . $filename;
    $configuredBase = rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/');
    if ($configuredBase !== '') {
        return $configuredBase . $path;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return $path;
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host . $path;
}

function createTripWebpVariant(
    GdImage $source,
    int $sourceWidth,
    int $sourceHeight,
    string $uploadDirectory,
    string $suffix,
    int $maxWidth,
    int $maxHeight,
    int $targetBytes,
    int $initialQuality
): array
{
    $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $width = max(1, (int) round($sourceWidth * $scale));
    $height = max(1, (int) round($sourceHeight * $scale));
    $target = imagecreatetruecolor($width, $height);
    imagealphablending($target, false);
    imagesavealpha($target, true);
    $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
    imagefilledrectangle($target, 0, 0, $width, $height, $transparent);
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

    $filename = bin2hex(random_bytes(16)) . '-' . $suffix . '.webp';
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
    $quality = $initialQuality;
    $saved = false;
    do {
        $saved = imagewebp($target, $destination, $quality);
        clearstatcache(true, $destination);
        if (!$saved || !is_file($destination) || filesize($destination) <= $targetBytes) {
            break;
        }
        $quality -= 6;
    } while ($quality >= 54);
    imagedestroy($target);

    if (!$saved) {
        if (is_file($destination)) {
            unlink($destination);
        }
        throw new RuntimeException('Varian gambar WebP gagal dibuat.');
    }
    return [
        'path' => publicUploadPath('trips', $filename),
        'filename' => $filename,
        'width' => $width,
        'height' => $height,
    ];
}

function storeTripImageAsWebp(array $file, string $mime, string $uploadDirectory): ?array
{
    if (!function_exists('imagewebp') || !function_exists('imagecreatetruecolor')) {
        return null;
    }
    $loaders = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    $loader = $loaders[$mime] ?? null;
    if (!$loader || !function_exists($loader)) {
        return null;
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || empty($info[0]) || empty($info[1])) {
        return null;
    }
    $source = @$loader($file['tmp_name']);
    if (!$source) {
        return null;
    }
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        if ($orientation === 2 && function_exists('imageflip')) {
            imageflip($source, IMG_FLIP_HORIZONTAL);
        } elseif ($orientation === 3) {
            $rotated = imagerotate($source, 180, 0);
            if ($rotated !== false) {
                imagedestroy($source);
                $source = $rotated;
            }
        } elseif ($orientation === 4 && function_exists('imageflip')) {
            imageflip($source, IMG_FLIP_VERTICAL);
        } elseif (in_array($orientation, [5, 6], true)) {
            $rotated = imagerotate($source, -90, 0);
            if ($rotated !== false) {
                imagedestroy($source);
                $source = $rotated;
                if ($orientation === 5 && function_exists('imageflip')) {
                    imageflip($source, IMG_FLIP_HORIZONTAL);
                }
            }
        } elseif (in_array($orientation, [7, 8], true)) {
            $rotated = imagerotate($source, 90, 0);
            if ($rotated !== false) {
                imagedestroy($source);
                $source = $rotated;
                if ($orientation === 7 && function_exists('imageflip')) {
                    imageflip($source, IMG_FLIP_HORIZONTAL);
                }
            }
        }
    }
    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $detail = null;
    $thumbnail = null;
    try {
        $detail = createTripWebpVariant(
            $source,
            $sourceWidth,
            $sourceHeight,
            $uploadDirectory,
            'detail',
            1600,
            1000,
            700 * 1024,
            84
        );
        $thumbnail = createTripWebpVariant(
            $source,
            $sourceWidth,
            $sourceHeight,
            $uploadDirectory,
            'thumb',
            800,
            600,
            300 * 1024,
            80
        );
    } catch (Throwable $exception) {
        foreach ([$detail, $thumbnail] as $variant) {
            if (is_array($variant) && !empty($variant['filename'])) {
                $path = $uploadDirectory . DIRECTORY_SEPARATOR . $variant['filename'];
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
        imagedestroy($source);
        throw $exception;
    }
    imagedestroy($source);
    return [
        'path' => $detail['path'],
        'filename' => $detail['filename'],
        'width' => $detail['width'],
        'height' => $detail['height'],
        'thumbnailPath' => $thumbnail['path'],
        'thumbnailFilename' => $thumbnail['filename'],
        'thumbnailWidth' => $thumbnail['width'],
        'thumbnailHeight' => $thumbnail['height'],
    ];
}

function storeUploadedImage(array $file, string $folder, int $maxSizeMb = 5): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('File upload tidak ditemukan atau gagal diunggah.');
    }
    if (($file['size'] ?? 0) > $maxSizeMb * 1024 * 1024) {
        throw new InvalidArgumentException("Ukuran file maksimal {$maxSizeMb}MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Format file harus jpg, jpeg, png, atau webp.');
    }

    $uploadDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . trim($folder, '/');
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }
    if (trim($folder, '/') === 'trips') {
        $optimized = storeTripImageAsWebp($file, $mime, $uploadDirectory);
        if ($optimized !== null) {
            return $optimized;
        }
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('File gagal disimpan.');
    }
    return ['path' => publicUploadPath($folder, $filename), 'filename' => $filename];
}

function deleteStoredUpload(string $url, string $folder): void
{
    $path = parse_url($url, PHP_URL_PATH);
    $expectedPrefix = '/uploads/' . trim($folder, '/') . '/';
    if (!is_string($path) || !str_starts_with($path, $expectedPrefix)) {
        return;
    }

    $filename = basename($path);
    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }
    $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . trim($folder, '/');
    $target = $directory . DIRECTORY_SEPARATOR . $filename;
    $resolvedDirectory = realpath($directory);
    $resolvedTarget = realpath($target);
    if ($resolvedDirectory && $resolvedTarget && str_starts_with($resolvedTarget, $resolvedDirectory . DIRECTORY_SEPARATOR) && is_file($resolvedTarget)) {
        unlink($resolvedTarget);
    }
}

function mapTrip(PDO $pdo, array $trip, bool $customerView = false): array
{
    $tripId = (int) $trip['id'];
    $query = static function (string $sql) use ($pdo, $tripId): array {
        $statement = $pdo->prepare($sql);
        $statement->execute([$tripId]);
        return $statement->fetchAll();
    };
    $images = $query('SELECT id, image_url, thumbnail_url, sort_order FROM trip_images WHERE trip_id = ? ORDER BY sort_order, id');
    $schedules = $query('SELECT id, schedule_code, session_name, schedule_date, start_time, end_time, visible_until, archived_at, quota, booked_count, status FROM trip_schedules WHERE trip_id = ? ORDER BY schedule_date, start_time, id');
    $sessions = $query('SELECT id, session_code, name, start_time, end_time, status FROM trip_sessions WHERE trip_id = ? ORDER BY start_time, id');
    $packages = $query('SELECT id, package_code, name, price, max_custom_pax, destinations_json, description, status, sort_order FROM private_trip_packages WHERE trip_id = ? ORDER BY sort_order, id');
    $packageTiers = $query(
        'SELECT ppt.package_id, ppt.pax_count, ppt.price_per_person
         FROM package_price_tiers ppt
         INNER JOIN private_trip_packages ptp ON ptp.id = ppt.package_id
         WHERE ptp.trip_id = ? ORDER BY ppt.package_id, ppt.pax_count'
    );
    $tiers = $query('SELECT pax_count, price_per_person FROM private_price_tiers WHERE trip_id = ? ORDER BY pax_count');
    $addons = $query("SELECT id, name, price, worker_action, status, sort_order FROM trip_addons WHERE trip_id = ? AND status = 'active' ORDER BY sort_order, id");
    $tierMap = [];
    foreach ($tiers as $tier) {
        $tierMap[(string) $tier['pax_count']] = (float) $tier['price_per_person'];
    }
    $now = appNow();
    $allMappedSchedules = array_map(static function (array $item) use ($now): array {
        $lifecycleStatus = scheduleLifecycleStatus($item, $now);
        return [
            'id' => $item['schedule_code'] ?: (string) $item['id'],
            'databaseId' => (int) $item['id'],
            'name' => $item['session_name'] ?: 'Sesi 1',
            'date' => $item['schedule_date'],
            'startTime' => $item['start_time'] ? substr((string) $item['start_time'], 0, 5) : '',
            'endTime' => $item['end_time'] ? substr((string) $item['end_time'], 0, 5) : '',
            'visibleUntil' => $item['visible_until'] ?? null,
            'isArchived' => $lifecycleStatus === 'archived',
            'lifecycleStatus' => $lifecycleStatus,
            'quota' => (int) $item['quota'],
            'bookedCount' => (int) $item['booked_count'],
            'status' => $item['status'],
            'isBookable' => scheduleIsBookable($item, $now),
        ];
    }, $schedules);
    $mappedSchedules = $customerView
        ? array_values(array_filter(
            $allMappedSchedules,
            static fn(array $item): bool => $item['lifecycleStatus'] === 'upcoming' && $item['status'] !== 'inactive'
        ))
        : $allMappedSchedules;
    $allMappedSessions = array_map(static fn(array $item): array => [
        'id' => $item['session_code'] ?: (string) $item['id'],
        'databaseId' => (int) $item['id'],
        'name' => $item['name'],
        'startTime' => substr((string) $item['start_time'], 0, 5),
        'endTime' => substr((string) $item['end_time'], 0, 5),
        'status' => $item['status'],
    ], $sessions);
    $mappedSessions = $customerView
        ? array_values(array_filter($allMappedSessions, static fn(array $item): bool => $item['status'] === 'active'))
        : $allMappedSessions;
    $lifecycleStatus = tripLifecycleStatus($trip, $schedules, $sessions, $now);
    $lastEndAt = tripLastEndAt($trip, $schedules, $sessions);
    $archiveEligibleAt = $lastEndAt?->modify('+1 day');
    $permanentDeleteEligibleAt = $lastEndAt?->modify('+37 days');
    $hasBookableSchedule = ($trip['trip_type'] ?? 'open') === 'private'
        ? privateTripHasBookableSlot($trip, $sessions, $now)
        : (bool) array_filter($allMappedSchedules, static fn(array $item): bool => $item['isBookable']);

    return [
        'id' => $tripId,
        'name' => $trip['name'],
        'type' => $trip['trip_type'],
        'isPrivateTrip' => $trip['trip_type'] === 'private',
        'experienceType' => $trip['experience_type'],
        'status' => $trip['status'],
        'isArchived' => $lifecycleStatus === 'archived',
        'lifecycleStatus' => $lifecycleStatus,
        'hasBookableSchedule' => $hasBookableSchedule,
        'archiveEligibleAt' => $archiveEligibleAt?->format(DATE_ATOM),
        'permanentDeleteEligibleAt' => $permanentDeleteEligibleAt?->format(DATE_ATOM),
        'canPermanentlyDelete' => $lifecycleStatus === 'archived'
            && $permanentDeleteEligibleAt !== null
            && $permanentDeleteEligibleAt <= $now,
        'destination' => ['id' => $trip['destination_id'] ?? '', 'en' => $trip['destination_en'] ?? ''],
        'description' => ['id' => $trip['description_id'] ?? '', 'en' => $trip['description_en'] ?? ''],
        'activities' => ['id' => decodeText($trip['activities_id'] ?? '') ?: [], 'en' => decodeText($trip['activities_en'] ?? '') ?: []],
        'facilities' => ['id' => decodeText($trip['facilities_id'] ?? '') ?: [], 'en' => decodeText($trip['facilities_en'] ?? '') ?: []],
        'price' => (float) $trip['price'],
        'quota' => $customerView && ($trip['trip_type'] ?? 'open') === 'open'
            ? array_sum(array_column($mappedSchedules, 'quota'))
            : (int) $trip['quota'],
        'slots' => $customerView && ($trip['trip_type'] ?? 'open') === 'open'
            ? array_sum(array_map(
                static fn(array $schedule): int => max($schedule['quota'] - $schedule['bookedCount'], 0),
                $mappedSchedules
            ))
            : (int) $trip['slots'],
        'minParticipants' => (int) $trip['min_participants'],
        'maxParticipants' => (int) $trip['max_participants'],
        'maxCustomPax' => (int) $trip['max_custom_pax'],
        'availableStartDate' => $trip['available_start_date'],
        'availableEndDate' => $trip['available_end_date'],
        'privateNotes' => $trip['private_notes'] ?? '',
        'flexibleSchedule' => (bool) $trip['flexible_schedule'],
        'privateBookingMode' => ($trip['private_booking_mode'] ?? 'exclusive') === 'shared' ? 'shared' : 'exclusive',
        'h7ReminderSubject' => $trip['h7_reminder_subject'] ?? '',
        'h7ReminderBody' => $trip['h7_reminder_body'] ?? '',
        'date' => $mappedSchedules[0]['date'] ?? '',
        'imageUrl' => $images[0]['image_url'] ?? '',
        'imageUrls' => array_column($images, 'image_url'),
        'thumbnailUrl' => $images[0]['thumbnail_url'] ?? ($images[0]['image_url'] ?? ''),
        'thumbnailUrls' => array_map(
            static fn(array $image): string => (string) ($image['thumbnail_url'] ?: $image['image_url']),
            $images
        ),
        'schedules' => $mappedSchedules,
        'sessions' => $mappedSessions,
        'privatePackages' => array_map(static function (array $item) use ($packageTiers): array {
            $tierMap = [];
            foreach ($packageTiers as $tier) {
                if ((int) $tier['package_id'] === (int) $item['id']) {
                    $tierMap[(string) $tier['pax_count']] = (float) $tier['price_per_person'];
                }
            }
            return [
                'id' => (int) $item['id'],
                'packageCode' => $item['package_code'],
                'name' => $item['name'],
                'price' => (float) $item['price'],
                'maxCustomPax' => (int) $item['max_custom_pax'],
                'pricePerPersonTiers' => $tierMap,
                'destinations' => decodeText($item['destinations_json']) ?: [],
                'description' => $item['description'] ?? '',
                'status' => $item['status'],
                'sortOrder' => (int) $item['sort_order'],
            ];
        }, $packages),
        'pricePerPersonTiers' => $tierMap,
        'addons' => array_map(static fn(array $item): array => [
            'id' => (int) $item['id'],
            'name' => $item['name'],
            'label' => $item['name'],
            'price' => (float) $item['price'],
            'workerAction' => $item['worker_action'],
            'status' => $item['status'],
            'sortOrder' => (int) $item['sort_order'],
        ], $addons),
    ];
}

function mapTripSummaries(PDO $pdo, array $trips, bool $customerView = false): array
{
    if (!$trips) {
        return [];
    }
    $tripIds = array_map(static fn(array $trip): int => (int) $trip['id'], $trips);
    $placeholders = implode(',', array_fill(0, count($tripIds), '?'));
    $imageStatement = $pdo->prepare(
        "SELECT trip_id, image_url, thumbnail_url FROM trip_images
         WHERE trip_id IN ($placeholders) ORDER BY trip_id, sort_order, id"
    );
    $imageStatement->execute($tripIds);
    $images = [];
    foreach ($imageStatement->fetchAll() as $image) {
        $tripId = (int) $image['trip_id'];
        if (!isset($images[$tripId])) {
            $images[$tripId] = (string) ($image['thumbnail_url'] ?: $image['image_url']);
        }
    }
    $scheduleStatement = $pdo->prepare(
        "SELECT trip_id, schedule_code, session_name, schedule_date, start_time, end_time, visible_until,
                archived_at, quota, booked_count, status
         FROM trip_schedules WHERE trip_id IN ($placeholders)
         ORDER BY trip_id, schedule_date, start_time, id"
    );
    $scheduleStatement->execute($tripIds);
    $schedules = [];
    $now = appNow();
    foreach ($scheduleStatement->fetchAll() as $schedule) {
        $tripId = (int) $schedule['trip_id'];
        $lifecycleStatus = scheduleLifecycleStatus($schedule, $now);
        $schedules[$tripId][] = [
            'id' => $schedule['schedule_code'],
            'name' => $schedule['session_name'] ?: 'Sesi 1',
            'date' => $schedule['schedule_date'],
            'startTime' => $schedule['start_time'] ? substr((string) $schedule['start_time'], 0, 5) : '',
            'endTime' => $schedule['end_time'] ? substr((string) $schedule['end_time'], 0, 5) : '',
            'visibleUntil' => $schedule['visible_until'] ?? null,
            'isArchived' => $lifecycleStatus === 'archived',
            'lifecycleStatus' => $lifecycleStatus,
            'quota' => (int) $schedule['quota'],
            'bookedCount' => (int) $schedule['booked_count'],
            'status' => $schedule['status'],
            'isBookable' => scheduleIsBookable($schedule, $now),
        ];
    }
    $sessionStatement = $pdo->prepare(
        "SELECT trip_id, start_time, end_time, status
         FROM trip_sessions WHERE trip_id IN ($placeholders)
         ORDER BY trip_id, start_time, id"
    );
    $sessionStatement->execute($tripIds);
    $sessions = [];
    foreach ($sessionStatement->fetchAll() as $session) {
        $sessions[(int) $session['trip_id']][] = $session;
    }
    return array_map(static function (array $trip) use ($images, $schedules, $sessions, $customerView, $now): array {
        $tripId = (int) $trip['id'];
        $allTripSchedules = $schedules[$tripId] ?? [];
        $tripSchedules = $customerView
            ? array_values(array_filter(
                $allTripSchedules,
                static fn(array $schedule): bool => $schedule['lifecycleStatus'] === 'upcoming' && $schedule['status'] !== 'inactive'
            ))
            : $allTripSchedules;
        $tripSessions = $sessions[$tripId] ?? [];
        $isPrivate = ($trip['trip_type'] ?? 'open') === 'private';
        $lifecycleStatus = tripLifecycleStatus($trip, $allTripSchedules, $tripSessions, $now);
        $lastEndAt = tripLastEndAt($trip, $allTripSchedules, $tripSessions);
        $archiveEligibleAt = $lastEndAt?->modify('+1 day');
        $permanentDeleteEligibleAt = $lastEndAt?->modify('+37 days');
        $hasBookableSchedule = $isPrivate
            ? privateTripHasBookableSlot($trip, $tripSessions, $now)
            : (bool) array_filter($allTripSchedules, static fn(array $schedule): bool => $schedule['isBookable']);
        return [
            'id' => $tripId,
            'name' => $trip['name'],
            'type' => $trip['trip_type'],
            'isPrivateTrip' => $isPrivate,
            'experienceType' => $trip['experience_type'],
            'status' => $trip['status'],
            'isArchived' => $lifecycleStatus === 'archived',
            'lifecycleStatus' => $lifecycleStatus,
            'hasBookableSchedule' => $hasBookableSchedule,
            'archiveEligibleAt' => $archiveEligibleAt?->format(DATE_ATOM),
            'permanentDeleteEligibleAt' => $permanentDeleteEligibleAt?->format(DATE_ATOM),
            'canPermanentlyDelete' => $lifecycleStatus === 'archived'
                && $permanentDeleteEligibleAt !== null
                && $permanentDeleteEligibleAt <= $now,
            'destination' => ['id' => $trip['destination_id'] ?? '', 'en' => $trip['destination_en'] ?? ''],
            'price' => (float) $trip['price'],
            'quota' => $customerView && !$isPrivate
                ? array_sum(array_column($tripSchedules, 'quota'))
                : (int) $trip['quota'],
            'slots' => $customerView && !$isPrivate
                ? array_sum(array_map(
                    static fn(array $schedule): int => max($schedule['quota'] - $schedule['bookedCount'], 0),
                    $tripSchedules
                ))
                : (int) $trip['slots'],
            'minParticipants' => (int) $trip['min_participants'],
            'maxParticipants' => (int) $trip['max_participants'],
            'availableStartDate' => $trip['available_start_date'],
            'availableEndDate' => $trip['available_end_date'],
            'privateBookingMode' => ($trip['private_booking_mode'] ?? 'exclusive') === 'shared' ? 'shared' : 'exclusive',
            'date' => $tripSchedules[0]['date'] ?? '',
            'imageUrl' => $images[$tripId] ?? '',
            'schedules' => $tripSchedules,
        ];
    }, $trips);
}

function mapBooking(PDO $pdo, array $booking): array
{
    return mapBookings($pdo, [$booking])[0];
}

function mapBookings(PDO $pdo, array $bookings): array
{
    if (!$bookings) {
        return [];
    }
    $bookingIds = array_map(static fn(array $booking): int => (int) $booking['id'], $bookings);
    $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
    $tripIds = array_values(array_unique(array_map(static fn(array $booking): int => (int) $booking['trip_id'], $bookings)));
    $tripPlaceholders = implode(',', array_fill(0, count($tripIds), '?'));
    $tripStatement = $pdo->prepare(
        "SELECT id, name, destination_id, destination_en FROM trips WHERE id IN ($tripPlaceholders)"
    );
    $tripStatement->execute($tripIds);
    $bookingTrips = [];
    foreach ($tripStatement->fetchAll() as $trip) {
        $bookingTrips[(int) $trip['id']] = $trip;
    }
    $userIds = array_values(array_unique(array_filter(array_map(
        static fn(array $booking): int => (int) ($booking['user_id'] ?? 0),
        $bookings
    ))));
    $userProfiles = [];
    if ($userIds) {
        $userPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $userStatement = $pdo->prepare(
            "SELECT id, blood_type, height_cm, weight_kg, shoe_size
             FROM users WHERE id IN ($userPlaceholders)"
        );
        $userStatement->execute($userIds);
        foreach ($userStatement->fetchAll() as $userProfile) {
            $userProfiles[(int) $userProfile['id']] = $userProfile;
        }
    }
    $payments = [];
    $paymentStatement = $pdo->prepare(
        "SELECT booking_id, amount, payment_method, payment_proof_url, payment_status, submitted_at
         FROM payments WHERE booking_id IN ($placeholders) ORDER BY booking_id, id DESC"
    );
    $paymentStatement->execute($bookingIds);
    foreach ($paymentStatement->fetchAll() as $payment) {
        $bookingId = (int) $payment['booking_id'];
        if (!isset($payments[$bookingId])) {
            $payments[$bookingId] = $payment;
        }
    }
    $participants = [];
    $participantStatement = $pdo->prepare(
        "SELECT booking_id, name, address, age, gender, health_notes
         FROM booking_participants WHERE booking_id IN ($placeholders) ORDER BY booking_id, id"
    );
    $participantStatement->execute($bookingIds);
    foreach ($participantStatement->fetchAll() as $participant) {
        $participants[(int) $participant['booking_id']][] = $participant;
    }
    $addons = [];
    $addonStatement = $pdo->prepare(
        "SELECT ba.booking_id, ba.addon_id, ba.trip_addon_id, ba.price,
                COALESCE(ta.name, a.label, ba.addon_id) addon_name,
                COALESCE(ta.worker_action, 'none') worker_action
         FROM booking_addons ba
         LEFT JOIN trip_addons ta ON ta.id = ba.trip_addon_id
         LEFT JOIN addons a ON a.id = ba.addon_id
         WHERE ba.booking_id IN ($placeholders) ORDER BY ba.booking_id, ba.id"
    );
    $addonStatement->execute($bookingIds);
    foreach ($addonStatement->fetchAll() as $addon) {
        $addons[(int) $addon['booking_id']][] = $addon;
    }
    $scheduleIds = array_values(array_unique(array_filter(array_map(
        static fn(array $booking): int => (int) ($booking['schedule_id'] ?? 0),
        $bookings
    ))));
    $schedules = [];
    if ($scheduleIds) {
        $schedulePlaceholders = implode(',', array_fill(0, count($scheduleIds), '?'));
        $statement = $pdo->prepare("SELECT id, schedule_code, session_name FROM trip_schedules WHERE id IN ($schedulePlaceholders)");
        $statement->execute($scheduleIds);
        foreach ($statement->fetchAll() as $schedule) {
            $schedules[(int) $schedule['id']] = $schedule;
        }
    }
    $sessionIds = array_values(array_unique(array_filter(array_map(
        static fn(array $booking): int => (int) ($booking['session_id'] ?? 0),
        $bookings
    ))));
    $sessions = [];
    if ($sessionIds) {
        $sessionPlaceholders = implode(',', array_fill(0, count($sessionIds), '?'));
        $statement = $pdo->prepare(
            "SELECT id, session_code, name, start_time, end_time
             FROM trip_sessions WHERE id IN ($sessionPlaceholders)"
        );
        $statement->execute($sessionIds);
        foreach ($statement->fetchAll() as $session) {
            $sessions[(int) $session['id']] = $session;
        }
    }
    return array_map(static function (array $booking) use ($payments, $participants, $addons, $schedules, $sessions, $userProfiles, $bookingTrips): array {
        $id = (int) $booking['id'];
        $payment = $payments[$id] ?? [];
        $bookingParticipants = $participants[$id] ?? [];
        $bookingAddons = $addons[$id] ?? [];
        $schedule = $schedules[(int) ($booking['schedule_id'] ?? 0)] ?? [];
        $session = $sessions[(int) ($booking['session_id'] ?? 0)] ?? [];
        $userProfile = $userProfiles[(int) ($booking['user_id'] ?? 0)] ?? [];
        $trip = $bookingTrips[(int) $booking['trip_id']] ?? [];
        return mapBookingRecord($booking, $payment, $bookingParticipants, $bookingAddons, $schedule, $session, $userProfile, $trip);
    }, $bookings);
}

function mapBookingRecord(
    array $booking,
    array $payment,
    array $participants,
    array $bookingAddons,
    array $schedule,
    array $session,
    array $userProfile = [],
    array $trip = []
): array {
    $id = (int) $booking['id'];
    $primary = $participants[0] ?? [];
    $bookingDate = (string) ($booking['selected_date'] ?? substr((string) ($booking['created_at'] ?? date('Y-m-d')), 0, 10));
    $endAt = scheduledEndAt($bookingDate, $booking['end_time'] ?? null);
    $now = appNow();
    $isCompleted = $endAt <= $now;
    $isArchived = !empty($booking['archived_at']) || $endAt->modify('+1 day') < $now;
    return [
        'id' => $id,
        'userId' => (int) $booking['user_id'],
        'tripId' => (int) $booking['trip_id'],
        'tripName' => $trip['name'] ?? '',
        'tripDestination' => [
            'id' => $trip['destination_id'] ?? '',
            'en' => $trip['destination_en'] ?? '',
        ],
        'scheduleDatabaseId' => nullableInt($booking['schedule_id']),
        'sessionDatabaseId' => nullableInt($booking['session_id']),
        'scheduleId' => $schedule['schedule_code'] ?? '',
        'sessionId' => $session['session_code'] ?? '',
        'sessionName' => $booking['trip_type'] === 'open'
            ? ($schedule['session_name'] ?? 'Sesi 1')
            : ($session['name'] ?? ''),
        'selectedPackageId' => nullableInt($booking['selected_package_id'] ?? null),
        'selectedPackageName' => $booking['selected_package_name'] ?? '',
        'selectedPackagePrice' => (float) ($booking['selected_package_price'] ?? 0),
        'selectedPackageSubtotal' => (float) ($booking['selected_package_subtotal'] ?? 0),
        'selectedPackageDestinations' => decodeText($booking['selected_package_destinations'] ?? '') ?: [],
        'name' => $booking['customer_name'],
        'email' => $booking['customer_email'],
        'whatsapp' => $booking['customer_whatsapp'],
        'tripType' => $booking['trip_type'],
        'experienceType' => $booking['experience_type'],
        'selectedDate' => $booking['selected_date'],
        'requestedDate' => $booking['selected_date'],
        'visibleUntil' => $booking['visible_until'] ?? null,
        'isCompleted' => $isCompleted,
        'isArchived' => $isArchived,
        'lifecycleStatus' => $isArchived ? 'archived' : ($isCompleted ? 'completed' : 'upcoming'),
        'startTime' => $booking['start_time'] ? substr((string) $booking['start_time'], 0, 5) : '',
        'endTime' => $booking['end_time'] ? substr((string) $booking['end_time'], 0, 5) : '',
        'participants' => (int) $booking['participants'],
        'participantCount' => (int) $booking['participants'],
        'pricePerPerson' => (float) $booking['price_per_person'],
        'hargaPerOrang' => (float) $booking['price_per_person'],
        'totalPrice' => (float) $booking['total_price'],
        'totalHarga' => (float) $booking['total_price'],
        'paymentType' => $booking['payment_type'] ?? '',
        'paymentStatus' => $booking['payment_status'] ?? ($payment['payment_status'] ?? ''),
        'paymentProofUrl' => $payment['payment_proof_url'] ?? '',
        'paymentMethod' => $payment['payment_method'] ?? '',
        'requiredPaymentAmount' => (float) ($booking['required_payment_amount'] ?? ($payment['amount'] ?? 0)),
        'paidAmount' => (float) ($booking['paid_amount'] ?? ($payment['amount'] ?? 0)),
        'bcaAccountNumber' => $booking['bca_account_number'] ?? '',
        'paymentSubmittedAt' => $payment['submitted_at'] ?? null,
        'createdAt' => $booking['created_at'] ?? null,
        'status' => $booking['status'],
        'notes' => $booking['notes'] ?? '',
        'transportFrom' => $booking['transport_from'] ?? '',
        'address' => $primary['address'] ?? '',
        'age' => $primary['age'] ?? '',
        'gender' => $primary['gender'] ?? '',
        'healthNotes' => $primary['health_notes'] ?? '',
        'bloodType' => $userProfile['blood_type'] ?? '',
        'heightCm' => $userProfile['height_cm'] ?? '',
        'weightKg' => $userProfile['weight_kg'] ?? '',
        'shoeSize' => $userProfile['shoe_size'] ?? '',
        'isPrivateTour' => $booking['trip_type'] === 'private',
        'isPrivateTrip' => $booking['trip_type'] === 'private',
        'participantDetails' => array_map(static fn(array $item): array => [
            'name' => $item['name'],
            'address' => $item['address'] ?? '',
            'age' => $item['age'] ?? '',
            'gender' => $item['gender'] ?? '',
            'healthNotes' => $item['health_notes'] ?? '',
        ], $participants),
        'addons' => array_map(static fn(array $item): int|string => $item['trip_addon_id'] ? (int) $item['trip_addon_id'] : $item['addon_id'], $bookingAddons),
        'addonDetails' => array_map(static fn(array $item): array => [
            'id' => $item['trip_addon_id'] ? (int) $item['trip_addon_id'] : $item['addon_id'],
            'name' => $item['addon_name'],
            'label' => $item['addon_name'],
            'price' => (float) $item['price'],
            'workerAction' => $item['worker_action'],
        ], $bookingAddons),
    ];
}

function mapWorkerTask(array $task): array
{
    return [
        'id' => (int) $task['id'],
        'bookingId' => (int) $task['booking_id'],
        'registrationId' => (int) $task['booking_id'],
        'tripId' => (int) $task['trip_id'],
        'addonId' => $task['addon_id'],
        'tripAddonId' => nullableInt($task['trip_addon_id'] ?? null),
        'addonType' => $task['trip_addon_id'] ?? $task['addon_id'],
        'addonLabel' => $task['addon_name'] ?? $task['addon_label'] ?? $task['addon_id'],
        'workerAction' => $task['worker_action'] ?? 'none',
        'workerId' => nullableInt($task['worker_id']),
        'worker' => $task['worker_name'] ?? '',
        'slot' => (int) $task['slot'],
        'totalWorkers' => (int) ($task['total_workers'] ?? 1),
        'task' => $task['task'],
        'status' => $task['status'],
        'resultLink' => $task['result_link'] ?? '',
        'driveLink' => $task['drive_link'] ?? '',
        'proofPhotoUrl' => $task['proof_photo_url'] ?? '',
        'proofPhotoName' => $task['proof_photo_name'] ?? '',
        'completionChecked' => (bool) $task['completion_checked'],
        'resultStatus' => $task['result_status'] ?? '',
        'completedAt' => $task['completed_at'],
        'completedByName' => $task['completed_by_name'] ?? '',
        'createdAt' => $task['created_at'],
        'updatedAt' => $task['updated_at'],
    ];
}
