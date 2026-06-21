<?php
declare(strict_types=1);

function saveTripRecord(PDO $pdo, array $data, ?int $tripId = null): int
{
    requiredFields($data, ['name', 'price']);
    $h7ReminderSubject = trim((string) ($data['h7ReminderSubject'] ?? ''));
    $h7ReminderSubjectLength = function_exists('mb_strlen')
        ? mb_strlen($h7ReminderSubject, 'UTF-8')
        : strlen($h7ReminderSubject);
    if ($h7ReminderSubjectLength > 190) {
        throw new InvalidArgumentException('Subject Email Pengingat H-7 maksimal 190 karakter.');
    }
    if (preg_match('/[\r\n]/', $h7ReminderSubject)) {
        throw new InvalidArgumentException('Subject Email Pengingat H-7 tidak boleh memiliki baris baru.');
    }
    $h7ReminderBody = trim(str_replace(["\r\n", "\r"], "\n", (string) ($data['h7ReminderBody'] ?? '')));
    $type = ($data['type'] ?? '') === 'private' || !empty($data['isPrivateTrip']) ? 'private' : 'open';
    $experienceType = ($data['experienceType'] ?? '') === 'custom' ? 'custom' : 'cave';
    $destination = is_array($data['destination'] ?? null) ? $data['destination'] : ['id' => $data['destination'] ?? '', 'en' => ''];
    $description = is_array($data['description'] ?? null) ? $data['description'] : ['id' => $data['description'] ?? '', 'en' => ''];
    $activities = is_array($data['activities'] ?? null) ? $data['activities'] : ['id' => [], 'en' => []];
    $facilities = is_array($data['facilities'] ?? null) ? $data['facilities'] : ['id' => [], 'en' => []];
    $schedules = $type === 'open' && is_array($data['schedules'] ?? null) ? $data['schedules'] : [];
    $sessions = $type === 'private' && is_array($data['sessions'] ?? null) ? $data['sessions'] : [];
    $packages = $type === 'private' && is_array($data['privatePackages'] ?? null) ? $data['privatePackages'] : [];
    $privateBookingMode = $type === 'private' && ($data['privateBookingMode'] ?? '') === 'shared'
        ? 'shared'
        : 'exclusive';
    $quota = $type === 'open'
        ? array_sum(array_map(static fn(array $item): int => (int) ($item['quota'] ?? 0), $schedules))
        : (int) ($data['quota'] ?? $data['maxParticipants'] ?? 1);
    $slots = $type === 'open'
        ? array_sum(array_map(static fn(array $item): int => max((int) ($item['quota'] ?? 0) - (int) ($item['bookedCount'] ?? 0), 0), $schedules))
        : (int) ($data['slots'] ?? $quota);

    $values = [
        $data['name'],
        $type,
        $experienceType,
        $data['status'] ?? 'Tersedia',
        $destination['id'] ?? '',
        $destination['en'] ?? '',
        $description['id'] ?? '',
        $description['en'] ?? '',
        jsonText($activities['id'] ?? []),
        jsonText($activities['en'] ?? []),
        jsonText($facilities['id'] ?? []),
        jsonText($facilities['en'] ?? []),
        (float) $data['price'],
        $quota,
        $slots,
        (int) ($data['minParticipants'] ?? 1),
        (int) ($data['maxParticipants'] ?? $quota),
        (int) ($data['maxCustomPax'] ?? 0),
        $type === 'private' ? ($data['availableStartDate'] ?? null) : null,
        $type === 'private' ? ($data['availableEndDate'] ?? null) : null,
        $type === 'private' ? ($data['privateNotes'] ?? '') : '',
        $type === 'private' ? 1 : boolValue($data['flexibleSchedule'] ?? false),
        $privateBookingMode,
        $h7ReminderSubject !== '' ? $h7ReminderSubject : null,
        $h7ReminderBody !== '' ? $h7ReminderBody : null,
    ];

    if ($tripId === null) {
        $statement = $pdo->prepare(
            'INSERT INTO trips
            (name, trip_type, experience_type, status, destination_id, destination_en, description_id, description_en,
             activities_id, activities_en, facilities_id, facilities_en, price, quota, slots, min_participants,
             max_participants, max_custom_pax, available_start_date, available_end_date, private_notes, flexible_schedule,
             private_booking_mode, h7_reminder_subject, h7_reminder_body)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $statement->execute($values);
        $tripId = (int) $pdo->lastInsertId();
    } else {
        $statement = $pdo->prepare(
            'UPDATE trips SET name=?, trip_type=?, experience_type=?, status=?, destination_id=?, destination_en=?,
             description_id=?, description_en=?, activities_id=?, activities_en=?, facilities_id=?, facilities_en=?,
             price=?, quota=?, slots=?, min_participants=?, max_participants=?, max_custom_pax=?,
             available_start_date=?, available_end_date=?, private_notes=?, flexible_schedule=?,
             private_booking_mode=?, h7_reminder_subject=?, h7_reminder_body=?, updated_at=CURRENT_TIMESTAMP
             WHERE id=?'
        );
        $statement->execute([...$values, $tripId]);
        if ($statement->rowCount() === 0) {
            $exists = $pdo->prepare('SELECT id FROM trips WHERE id = ?');
            $exists->execute([$tripId]);
            if (!$exists->fetch()) {
                throw new InvalidArgumentException('Trip tidak ditemukan.');
            }
        }
    }

    $existingScheduleStatement = $pdo->prepare('SELECT id, schedule_code FROM trip_schedules WHERE trip_id = ?');
    $existingScheduleStatement->execute([$tripId]);
    $existingSchedules = [];
    foreach ($existingScheduleStatement->fetchAll() as $item) {
        $existingSchedules[$item['schedule_code']] = (int) $item['id'];
    }
    $scheduleInsert = $pdo->prepare(
        'INSERT INTO trip_schedules
         (trip_id, schedule_code, schedule_date, start_time, end_time, visible_until, archived_at, quota, booked_count, status)
         VALUES (?,?,?,?,?,DATE_ADD(?, INTERVAL 7 DAY),NULL,?,?,?)'
    );
    $scheduleUpdate = $pdo->prepare(
        'UPDATE trip_schedules
         SET schedule_code=?, schedule_date=?, start_time=?, end_time=?,
             visible_until=DATE_ADD(?, INTERVAL 7 DAY), archived_at=NULL,
             quota=?, booked_count=?, status=?
         WHERE id=? AND trip_id=?'
    );
    $retainedScheduleCodes = [];
    foreach ($schedules as $index => $schedule) {
        $code = (string) ($schedule['id'] ?? 'schedule_' . ($index + 1));
        $scheduleDate = trim((string) ($schedule['date'] ?? ''));
        $scheduleStartTime = trim((string) ($schedule['startTime'] ?? ''));
        $scheduleEndTime = trim((string) ($schedule['endTime'] ?? ''));
        if (
            $scheduleDate === ''
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $scheduleStartTime)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $scheduleEndTime)
            || $scheduleEndTime <= $scheduleStartTime
            || (int) ($schedule['quota'] ?? 0) <= 0
        ) {
            throw new InvalidArgumentException('Setiap jadwal Open Trip wajib memiliki tanggal, jam mulai, jam selesai, dan kuota yang valid.');
        }
        $retainedScheduleCodes[] = $code;
        $values = [
            $code,
            $scheduleDate,
            $scheduleStartTime,
            $scheduleEndTime,
            $scheduleDate,
            (int) ($schedule['quota'] ?? 0),
            (int) ($schedule['bookedCount'] ?? 0),
            in_array($schedule['status'] ?? 'active', ['active', 'full', 'inactive'], true) ? $schedule['status'] : 'active',
        ];
        if (isset($existingSchedules[$code])) {
            $scheduleUpdate->execute([...$values, $existingSchedules[$code], $tripId]);
            $scheduleDatabaseId = $existingSchedules[$code];
        } else {
            $scheduleInsert->execute([$tripId, ...$values]);
            $scheduleDatabaseId = (int) $pdo->lastInsertId();
        }
        $pdo->prepare(
            'UPDATE bookings
             SET start_time=COALESCE(start_time, ?), end_time=COALESCE(end_time, ?)
             WHERE schedule_id=?'
        )->execute([$scheduleStartTime, $scheduleEndTime, $scheduleDatabaseId]);
    }
    foreach ($existingSchedules as $code => $databaseId) {
        if (!in_array($code, $retainedScheduleCodes, true)) {
            $delete = $pdo->prepare('DELETE FROM trip_schedules WHERE id=? AND NOT EXISTS (SELECT 1 FROM bookings WHERE schedule_id=?)');
            $delete->execute([$databaseId, $databaseId]);
        }
    }

    $existingSessionStatement = $pdo->prepare('SELECT id, session_code FROM trip_sessions WHERE trip_id = ?');
    $existingSessionStatement->execute([$tripId]);
    $existingSessions = [];
    foreach ($existingSessionStatement->fetchAll() as $item) {
        $existingSessions[$item['session_code']] = (int) $item['id'];
    }
    $sessionInsert = $pdo->prepare(
        'INSERT INTO trip_sessions (trip_id, session_code, name, start_time, end_time, status) VALUES (?,?,?,?,?,?)'
    );
    $sessionUpdate = $pdo->prepare(
        'UPDATE trip_sessions SET session_code=?, name=?, start_time=?, end_time=?, status=? WHERE id=? AND trip_id=?'
    );
    $retainedSessionCodes = [];
    foreach ($sessions as $index => $session) {
        $code = (string) ($session['id'] ?? 'session_' . ($index + 1));
        $retainedSessionCodes[] = $code;
        $values = [
            $code, $session['name'] ?? 'Sesi ' . ($index + 1), $session['startTime'] ?? null,
            $session['endTime'] ?? null, ($session['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        ];
        if (isset($existingSessions[$code])) {
            $sessionUpdate->execute([...$values, $existingSessions[$code], $tripId]);
        } else {
            $sessionInsert->execute([$tripId, ...$values]);
        }
    }
    foreach ($existingSessions as $code => $databaseId) {
        if (!in_array($code, $retainedSessionCodes, true)) {
            $delete = $pdo->prepare('DELETE FROM trip_sessions WHERE id=? AND NOT EXISTS (SELECT 1 FROM bookings WHERE session_id=?)');
            $delete->execute([$databaseId, $databaseId]);
        }
    }

    $existingPackageStatement = $pdo->prepare('SELECT id, package_code FROM private_trip_packages WHERE trip_id = ?');
    $existingPackageStatement->execute([$tripId]);
    $existingPackages = [];
    foreach ($existingPackageStatement->fetchAll() as $item) {
        $existingPackages[$item['package_code']] = (int) $item['id'];
    }
    $packageInsert = $pdo->prepare(
        'INSERT INTO private_trip_packages
         (trip_id, package_code, name, price, max_custom_pax, destinations_json, description, status, sort_order)
         VALUES (?,?,?,?,?,?,?,?,?)'
    );
    $packageUpdate = $pdo->prepare(
        'UPDATE private_trip_packages
         SET package_code=?, name=?, price=?, max_custom_pax=?, destinations_json=?, description=?, status=?, sort_order=?
         WHERE id=? AND trip_id=?'
    );
    $retainedPackageCodes = [];
    foreach ($packages as $index => $package) {
        $code = trim((string) ($package['packageCode'] ?? $package['id'] ?? 'package_' . ($index + 1)));
        $name = trim((string) ($package['name'] ?? ''));
        $fallbackTierCount = count((array) ($package['pricePerPersonTiers'] ?? []));
        $maxCustomPax = max(1, (int) ($package['maxCustomPax'] ?? ($fallbackTierCount ?: 1)));
        $tierPrices = [];
        foreach ((array) ($package['pricePerPersonTiers'] ?? []) as $pax => $tierPrice) {
            if ((int) $pax > 0 && (float) $tierPrice > 0) {
                $tierPrices[(int) $pax] = (float) $tierPrice;
            }
        }
        $price = $tierPrices ? min($tierPrices) : (float) ($package['price'] ?? 0);
        $destinations = array_values(array_filter(array_map(
            static fn(mixed $value): string => trim((string) $value),
            (array) ($package['destinations'] ?? [])
        )));
        if ($name === '' || $price <= 0 || count($tierPrices) < $maxCustomPax || !$destinations) {
            throw new InvalidArgumentException('Setiap paket private wajib memiliki nama, tier harga per peserta, dan minimal satu destinasi/aktivitas.');
        }
        $retainedPackageCodes[] = $code;
        $values = [
            $code,
            $name,
            $price,
            $maxCustomPax,
            jsonText($destinations),
            trim((string) ($package['description'] ?? '')),
            ($package['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
            $index,
        ];
        if (isset($existingPackages[$code])) {
            $packageUpdate->execute([...$values, $existingPackages[$code], $tripId]);
        } else {
            $packageInsert->execute([$tripId, ...$values]);
            $existingPackages[$code] = (int) $pdo->lastInsertId();
        }
        $packageId = $existingPackages[$code];
        $existingTierStatement = $pdo->prepare('SELECT id, pax_count FROM package_price_tiers WHERE package_id=?');
        $existingTierStatement->execute([$packageId]);
        $existingPackageTiers = [];
        foreach ($existingTierStatement->fetchAll() as $tier) {
            $existingPackageTiers[(int) $tier['pax_count']] = (int) $tier['id'];
        }
        $tierInsert = $pdo->prepare(
            'INSERT INTO package_price_tiers (package_id, pax_count, price_per_person) VALUES (?,?,?)'
        );
        $tierUpdate = $pdo->prepare(
            'UPDATE package_price_tiers SET price_per_person=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND package_id=?'
        );
        foreach ($tierPrices as $pax => $tierPrice) {
            if (isset($existingPackageTiers[$pax])) {
                $tierUpdate->execute([$tierPrice, $existingPackageTiers[$pax], $packageId]);
            } else {
                $tierInsert->execute([$packageId, $pax, $tierPrice]);
            }
        }
    }
    foreach ($existingPackages as $code => $databaseId) {
        if (!in_array($code, $retainedPackageCodes, true)) {
            $used = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE selected_package_id = ?');
            $used->execute([$databaseId]);
            if ((int) $used->fetchColumn() > 0) {
                $pdo->prepare("UPDATE private_trip_packages SET status='inactive' WHERE id=? AND trip_id=?")
                    ->execute([$databaseId, $tripId]);
            } else {
                $pdo->prepare('DELETE FROM private_trip_packages WHERE id=? AND trip_id=?')
                    ->execute([$databaseId, $tripId]);
            }
        }
    }

    if (array_key_exists('pricePerPersonTiers', $data)) {
        $existingTierStatement = $pdo->prepare('SELECT id, pax_count FROM private_price_tiers WHERE trip_id=?');
        $existingTierStatement->execute([$tripId]);
        $existingTiers = [];
        foreach ($existingTierStatement->fetchAll() as $tier) {
            $existingTiers[(int) $tier['pax_count']] = (int) $tier['id'];
        }
        $tierInsert = $pdo->prepare('INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES (?,?,?)');
        $tierUpdate = $pdo->prepare('UPDATE private_price_tiers SET price_per_person=? WHERE id=? AND trip_id=?');
        foreach ((array) $data['pricePerPersonTiers'] as $pax => $price) {
            if ((int) $pax > 0 && (float) $price > 0) {
                if (isset($existingTiers[(int) $pax])) {
                    $tierUpdate->execute([(float) $price, $existingTiers[(int) $pax], $tripId]);
                } else {
                    $tierInsert->execute([$tripId, (int) $pax, (float) $price]);
                }
            }
        }
    }

    $incomingAddonIds = [];
    $addonInsert = $pdo->prepare(
        "INSERT INTO trip_addons (trip_id, name, price, worker_action, status, sort_order)
         VALUES (?,?,?,?, 'active', ?)"
    );
    $addonUpdate = $pdo->prepare(
        "UPDATE trip_addons SET name=?, price=?, worker_action=?, status='active', sort_order=?
         WHERE id=? AND trip_id=?"
    );
    foreach ((array) ($data['addons'] ?? []) as $index => $addon) {
        $name = trim((string) ($addon['name'] ?? $addon['label'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama add-on wajib diisi.');
        }
        $price = max(0, (float) ($addon['price'] ?? 0));
        $workerAction = ($addon['workerAction'] ?? 'none') === 'drive_link' ? 'drive_link' : 'none';
        $addonId = nullableInt($addon['id'] ?? null);
        if ($addonId) {
            $addonUpdate->execute([$name, $price, $workerAction, $index, $addonId, $tripId]);
            if ($addonUpdate->rowCount() === 0) {
                $exists = $pdo->prepare('SELECT id FROM trip_addons WHERE id=? AND trip_id=?');
                $exists->execute([$addonId, $tripId]);
                if (!$exists->fetch()) {
                    throw new InvalidArgumentException('Add-on trip tidak ditemukan.');
                }
            }
            $incomingAddonIds[] = $addonId;
        } else {
            $addonInsert->execute([$tripId, $name, $price, $workerAction, $index]);
            $incomingAddonIds[] = (int) $pdo->lastInsertId();
        }
    }
    if ($incomingAddonIds) {
        $placeholders = implode(',', array_fill(0, count($incomingAddonIds), '?'));
        $statement = $pdo->prepare("UPDATE trip_addons SET status='inactive' WHERE trip_id=? AND id NOT IN ($placeholders)");
        $statement->execute([$tripId, ...$incomingAddonIds]);
    } else {
        $pdo->prepare("UPDATE trip_addons SET status='inactive' WHERE trip_id=?")->execute([$tripId]);
    }

    $imageUrls = array_values(array_filter(array_map('trim', (array) ($data['imageUrls'] ?? []))));
    if (!$imageUrls && !empty($data['imageUrl'])) {
        $imageUrls = [(string) $data['imageUrl']];
    }
    if (array_key_exists('imageUrls', $data) || array_key_exists('imageUrl', $data)) {
        $existingImageStatement = $pdo->prepare('SELECT image_url, thumbnail_url FROM trip_images WHERE trip_id = ?');
        $existingImageStatement->execute([$tripId]);
        $existingImages = $existingImageStatement->fetchAll();
        $existingImageUrls = array_column($existingImages, 'image_url');
        $thumbnailByImageUrl = [];
        foreach ($existingImages as $existingImage) {
            $thumbnailByImageUrl[(string) $existingImage['image_url']] = $existingImage['thumbnail_url'] ?? null;
        }
        $pdo->prepare('DELETE FROM trip_images WHERE trip_id = ?')->execute([$tripId]);
        $imageStatement = $pdo->prepare('INSERT INTO trip_images (trip_id, image_url, thumbnail_url, sort_order) VALUES (?,?,?,?)');
        foreach ($imageUrls as $index => $url) {
            $imageStatement->execute([$tripId, $url, $thumbnailByImageUrl[$url] ?? null, $index]);
        }
        foreach (array_diff($existingImageUrls, $imageUrls) as $removedUrl) {
            deleteStoredUpload((string) $removedUrl, 'trips');
            $removedThumbnail = $thumbnailByImageUrl[$removedUrl] ?? null;
            if ($removedThumbnail) {
                deleteStoredUpload((string) $removedThumbnail, 'trips');
            }
        }
    }
    return $tripId;
}
