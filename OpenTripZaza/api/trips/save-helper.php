<?php
declare(strict_types=1);

function saveTripRecord(PDO $pdo, array $data, ?int $tripId = null): int
{
    requiredFields($data, ['name', 'price']);
    $type = ($data['type'] ?? '') === 'private' || !empty($data['isPrivateTrip']) ? 'private' : 'open';
    $experienceType = ($data['experienceType'] ?? '') === 'custom' ? 'custom' : 'cave';
    $destination = is_array($data['destination'] ?? null) ? $data['destination'] : ['id' => $data['destination'] ?? '', 'en' => ''];
    $description = is_array($data['description'] ?? null) ? $data['description'] : ['id' => $data['description'] ?? '', 'en' => ''];
    $activities = is_array($data['activities'] ?? null) ? $data['activities'] : ['id' => [], 'en' => []];
    $facilities = is_array($data['facilities'] ?? null) ? $data['facilities'] : ['id' => [], 'en' => []];
    $schedules = $type === 'open' && is_array($data['schedules'] ?? null) ? $data['schedules'] : [];
    $sessions = $type === 'private' && is_array($data['sessions'] ?? null) ? $data['sessions'] : [];
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
    ];

    if ($tripId === null) {
        $statement = $pdo->prepare(
            'INSERT INTO trips
            (name, trip_type, experience_type, status, destination_id, destination_en, description_id, description_en,
             activities_id, activities_en, facilities_id, facilities_en, price, quota, slots, min_participants,
             max_participants, max_custom_pax, available_start_date, available_end_date, private_notes, flexible_schedule)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $statement->execute($values);
        $tripId = (int) $pdo->lastInsertId();
    } else {
        $statement = $pdo->prepare(
            'UPDATE trips SET name=?, trip_type=?, experience_type=?, status=?, destination_id=?, destination_en=?,
             description_id=?, description_en=?, activities_id=?, activities_en=?, facilities_id=?, facilities_en=?,
             price=?, quota=?, slots=?, min_participants=?, max_participants=?, max_custom_pax=?,
             available_start_date=?, available_end_date=?, private_notes=?, flexible_schedule=?, updated_at=CURRENT_TIMESTAMP
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
        'INSERT INTO trip_schedules (trip_id, schedule_code, schedule_date, quota, booked_count, status) VALUES (?,?,?,?,?,?)'
    );
    $scheduleUpdate = $pdo->prepare(
        'UPDATE trip_schedules SET schedule_code=?, schedule_date=?, quota=?, booked_count=?, status=? WHERE id=? AND trip_id=?'
    );
    $retainedScheduleCodes = [];
    foreach ($schedules as $index => $schedule) {
        $code = (string) ($schedule['id'] ?? 'schedule_' . ($index + 1));
        $retainedScheduleCodes[] = $code;
        $values = [
            $code, $schedule['date'] ?? null, (int) ($schedule['quota'] ?? 0),
            (int) ($schedule['bookedCount'] ?? 0),
            in_array($schedule['status'] ?? 'active', ['active', 'full', 'inactive'], true) ? $schedule['status'] : 'active',
        ];
        if (isset($existingSchedules[$code])) {
            $scheduleUpdate->execute([...$values, $existingSchedules[$code], $tripId]);
        } else {
            $scheduleInsert->execute([$tripId, ...$values]);
        }
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

    $pdo->prepare('DELETE FROM private_price_tiers WHERE trip_id = ?')->execute([$tripId]);
    $tierStatement = $pdo->prepare('INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES (?,?,?)');
    foreach (($data['pricePerPersonTiers'] ?? []) as $pax => $price) {
        if ((int) $pax > 0 && (float) $price > 0) {
            $tierStatement->execute([$tripId, (int) $pax, (float) $price]);
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
        $existingImageStatement = $pdo->prepare('SELECT image_url FROM trip_images WHERE trip_id = ?');
        $existingImageStatement->execute([$tripId]);
        $existingImageUrls = array_column($existingImageStatement->fetchAll(), 'image_url');
        $pdo->prepare('DELETE FROM trip_images WHERE trip_id = ?')->execute([$tripId]);
        $imageStatement = $pdo->prepare('INSERT INTO trip_images (trip_id, image_url, sort_order) VALUES (?,?,?)');
        foreach ($imageUrls as $index => $url) {
            $imageStatement->execute([$tripId, $url, $index]);
        }
        foreach (array_diff($existingImageUrls, $imageUrls) as $removedUrl) {
            deleteStoredUpload((string) $removedUrl, 'trips');
        }
    }
    return $tripId;
}
