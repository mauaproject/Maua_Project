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

function storeUploadedImage(array $file, string $folder): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('File upload tidak ditemukan atau gagal diunggah.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Ukuran file maksimal 5MB.');
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

function mapTrip(PDO $pdo, array $trip): array
{
    $tripId = (int) $trip['id'];
    $query = static function (string $sql) use ($pdo, $tripId): array {
        $statement = $pdo->prepare($sql);
        $statement->execute([$tripId]);
        return $statement->fetchAll();
    };
    $images = $query('SELECT id, image_url, sort_order FROM trip_images WHERE trip_id = ? ORDER BY sort_order, id');
    $schedules = $query('SELECT id, schedule_code, schedule_date, visible_until, archived_at, quota, booked_count, status FROM trip_schedules WHERE trip_id = ? ORDER BY schedule_date, id');
    $sessions = $query('SELECT id, session_code, name, start_time, end_time, status FROM trip_sessions WHERE trip_id = ? ORDER BY start_time, id');
    $tiers = $query('SELECT pax_count, price_per_person FROM private_price_tiers WHERE trip_id = ? ORDER BY pax_count');
    $addons = $query("SELECT id, name, price, worker_action, status, sort_order FROM trip_addons WHERE trip_id = ? AND status = 'active' ORDER BY sort_order, id");
    $tierMap = [];
    foreach ($tiers as $tier) {
        $tierMap[(string) $tier['pax_count']] = (float) $tier['price_per_person'];
    }
    $mappedSchedules = array_map(static fn(array $item): array => [
        'id' => $item['schedule_code'] ?: (string) $item['id'],
        'databaseId' => (int) $item['id'],
        'date' => $item['schedule_date'],
        'visibleUntil' => $item['visible_until'] ?? null,
        'isArchived' => !empty($item['archived_at']) || (!empty($item['visible_until']) && $item['visible_until'] < date('Y-m-d')),
        'quota' => (int) $item['quota'],
        'bookedCount' => (int) $item['booked_count'],
        'status' => $item['status'],
    ], $schedules);
    $mappedSessions = array_map(static fn(array $item): array => [
        'id' => $item['session_code'] ?: (string) $item['id'],
        'databaseId' => (int) $item['id'],
        'name' => $item['name'],
        'startTime' => substr((string) $item['start_time'], 0, 5),
        'endTime' => substr((string) $item['end_time'], 0, 5),
        'status' => $item['status'],
    ], $sessions);

    $isArchived = false;
    if (($trip['trip_type'] ?? 'open') === 'open' && $mappedSchedules) {
        $isArchived = !array_filter($mappedSchedules, static fn(array $item): bool => !$item['isArchived']);
    } elseif (($trip['trip_type'] ?? '') === 'private' && !empty($trip['available_end_date'])) {
        $isArchived = date('Y-m-d', strtotime((string) $trip['available_end_date'] . ' +7 days')) < date('Y-m-d');
    }

    return [
        'id' => $tripId,
        'name' => $trip['name'],
        'type' => $trip['trip_type'],
        'isPrivateTrip' => $trip['trip_type'] === 'private',
        'experienceType' => $trip['experience_type'],
        'status' => $trip['status'],
        'isArchived' => $isArchived,
        'destination' => ['id' => $trip['destination_id'] ?? '', 'en' => $trip['destination_en'] ?? ''],
        'description' => ['id' => $trip['description_id'] ?? '', 'en' => $trip['description_en'] ?? ''],
        'activities' => ['id' => decodeText($trip['activities_id'] ?? '') ?: [], 'en' => decodeText($trip['activities_en'] ?? '') ?: []],
        'facilities' => ['id' => decodeText($trip['facilities_id'] ?? '') ?: [], 'en' => decodeText($trip['facilities_en'] ?? '') ?: []],
        'price' => (float) $trip['price'],
        'quota' => (int) $trip['quota'],
        'slots' => (int) $trip['slots'],
        'minParticipants' => (int) $trip['min_participants'],
        'maxParticipants' => (int) $trip['max_participants'],
        'maxCustomPax' => (int) $trip['max_custom_pax'],
        'availableStartDate' => $trip['available_start_date'],
        'availableEndDate' => $trip['available_end_date'],
        'privateNotes' => $trip['private_notes'] ?? '',
        'flexibleSchedule' => (bool) $trip['flexible_schedule'],
        'date' => $mappedSchedules[0]['date'] ?? '',
        'imageUrl' => $images[0]['image_url'] ?? '',
        'imageUrls' => array_column($images, 'image_url'),
        'schedules' => $mappedSchedules,
        'sessions' => $mappedSessions,
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

function mapBooking(PDO $pdo, array $booking): array
{
    $id = (int) $booking['id'];
    $participantsStmt = $pdo->prepare('SELECT name, address, age, gender, health_notes FROM booking_participants WHERE booking_id = ? ORDER BY id');
    $participantsStmt->execute([$id]);
    $participants = $participantsStmt->fetchAll();
    $addonsStmt = $pdo->prepare(
        "SELECT ba.addon_id, ba.trip_addon_id, ba.price,
                COALESCE(ta.name, a.label, ba.addon_id) addon_name,
                COALESCE(ta.worker_action, 'none') worker_action
         FROM booking_addons ba
         LEFT JOIN trip_addons ta ON ta.id = ba.trip_addon_id
         LEFT JOIN addons a ON a.id = ba.addon_id
         WHERE ba.booking_id = ?
         ORDER BY ba.id"
    );
    $addonsStmt->execute([$id]);
    $bookingAddons = $addonsStmt->fetchAll();
    $schedule = null;
    if ($booking['schedule_id']) {
        $statement = $pdo->prepare('SELECT schedule_code FROM trip_schedules WHERE id = ?');
        $statement->execute([(int) $booking['schedule_id']]);
        $schedule = $statement->fetch();
    }
    $session = null;
    if ($booking['session_id']) {
        $statement = $pdo->prepare('SELECT session_code, name, start_time, end_time FROM trip_sessions WHERE id = ?');
        $statement->execute([(int) $booking['session_id']]);
        $session = $statement->fetch();
    }
    $primary = $participants[0] ?? [];
    return [
        'id' => $id,
        'userId' => (int) $booking['user_id'],
        'tripId' => (int) $booking['trip_id'],
        'scheduleDatabaseId' => nullableInt($booking['schedule_id']),
        'sessionDatabaseId' => nullableInt($booking['session_id']),
        'scheduleId' => $schedule['schedule_code'] ?? '',
        'sessionId' => $session['session_code'] ?? '',
        'sessionName' => $session['name'] ?? '',
        'name' => $booking['customer_name'],
        'email' => $booking['customer_email'],
        'whatsapp' => $booking['customer_whatsapp'],
        'tripType' => $booking['trip_type'],
        'experienceType' => $booking['experience_type'],
        'selectedDate' => $booking['selected_date'],
        'requestedDate' => $booking['selected_date'],
        'visibleUntil' => $booking['visible_until'] ?? null,
        'isArchived' => !empty($booking['archived_at']) || (!empty($booking['visible_until']) && $booking['visible_until'] < date('Y-m-d')),
        'startTime' => $booking['start_time'] ? substr((string) $booking['start_time'], 0, 5) : '',
        'endTime' => $booking['end_time'] ? substr((string) $booking['end_time'], 0, 5) : '',
        'participants' => (int) $booking['participants'],
        'participantCount' => (int) $booking['participants'],
        'pricePerPerson' => (float) $booking['price_per_person'],
        'hargaPerOrang' => (float) $booking['price_per_person'],
        'totalPrice' => (float) $booking['total_price'],
        'totalHarga' => (float) $booking['total_price'],
        'status' => $booking['status'],
        'notes' => $booking['notes'] ?? '',
        'transportFrom' => $booking['transport_from'] ?? '',
        'address' => $primary['address'] ?? '',
        'age' => $primary['age'] ?? '',
        'gender' => $primary['gender'] ?? '',
        'healthNotes' => $primary['health_notes'] ?? '',
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
