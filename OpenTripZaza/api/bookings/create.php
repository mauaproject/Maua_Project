<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['tripId', 'name', 'email', 'whatsapp', 'participants']);
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email customer tidak valid.');
    }
    $pdo->beginTransaction();
    try {
        $primary = is_array($data['participantDetails'] ?? null) ? ($data['participantDetails'][0] ?? []) : [];
        $email = strtolower($data['email']);
        $userLookup = $pdo->prepare('SELECT id FROM users WHERE email = ? FOR UPDATE');
        $userLookup->execute([$email]);
        $userId = (int) $userLookup->fetchColumn();
        $userValues = [
            $data['name'], $data['whatsapp'],
            $primary['address'] ?? $data['address'] ?? null,
            nullableInt($primary['age'] ?? $data['age'] ?? null),
            $primary['gender'] ?? $data['gender'] ?? null,
            $primary['healthNotes'] ?? $data['healthNotes'] ?? null,
        ];
        if ($userId) {
            $userStatement = $pdo->prepare(
                'UPDATE users SET name=?, whatsapp=?, address=?, age=?, gender=?, health_notes=?, updated_at=CURRENT_TIMESTAMP WHERE id=?'
            );
            $userStatement->execute([...$userValues, $userId]);
        } else {
            $userStatement = $pdo->prepare(
                "INSERT INTO users (name, email, password_hash, whatsapp, role, address, age, gender, health_notes)
                 VALUES (?,?,?,?,'customer',?,?,?,?)"
            );
            $userStatement->execute([
                $data['name'], $email, password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                $data['whatsapp'], $primary['address'] ?? $data['address'] ?? null,
                nullableInt($primary['age'] ?? $data['age'] ?? null),
                $primary['gender'] ?? $data['gender'] ?? null,
                $primary['healthNotes'] ?? $data['healthNotes'] ?? null,
            ]);
            $userId = (int) $pdo->lastInsertId();
        }

        $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE id = ? FOR UPDATE');
        $tripStatement->execute([(int) $data['tripId']]);
        $trip = $tripStatement->fetch();
        if (!$trip || !in_array($trip['status'], ['Tersedia', 'Penuh'], true)) {
            throw new InvalidArgumentException('Trip tidak tersedia.');
        }

        $scheduleId = null;
        if (($data['tripType'] ?? 'open') === 'open') {
            $scheduleStatement = $pdo->prepare('SELECT * FROM trip_schedules WHERE trip_id = ? AND (schedule_code = ? OR id = ?) FOR UPDATE');
            $scheduleStatement->execute([(int) $data['tripId'], $data['scheduleId'] ?? '', nullableInt($data['scheduleId'] ?? null)]);
            $schedule = $scheduleStatement->fetch();
            if (!$schedule || $schedule['status'] !== 'active') {
                throw new InvalidArgumentException('Jadwal trip tidak tersedia.');
            }
            if (((int) $schedule['quota'] - (int) $schedule['booked_count']) < (int) $data['participants']) {
                throw new InvalidArgumentException('Slot jadwal tidak mencukupi.');
            }
            $scheduleId = (int) $schedule['id'];
        }

        $sessionId = null;
        if (($data['tripType'] ?? '') === 'private') {
            $sessionStatement = $pdo->prepare('SELECT id FROM trip_sessions WHERE trip_id = ? AND (session_code = ? OR id = ?) AND status = "active"');
            $sessionStatement->execute([(int) $data['tripId'], $data['sessionId'] ?? '', nullableInt($data['sessionId'] ?? null)]);
            $sessionId = (int) $sessionStatement->fetchColumn();
            if (!$sessionId) {
                throw new InvalidArgumentException('Sesi private trip tidak tersedia.');
            }
            $collision = $pdo->prepare(
                "SELECT COUNT(*) FROM bookings WHERE trip_id = ? AND session_id = ? AND selected_date = ?
                 AND status IN ('Menunggu Approval','Disetujui')"
            );
            $collision->execute([(int) $data['tripId'], $sessionId, $data['selectedDate'] ?? null]);
            if ((int) $collision->fetchColumn() > 0) {
                throw new InvalidArgumentException('Sesi pada tanggal tersebut sudah dipesan.');
            }
        }

        $statement = $pdo->prepare(
            'INSERT INTO bookings
            (user_id, trip_id, schedule_id, session_id, customer_name, customer_email, customer_whatsapp,
             trip_type, experience_type, selected_date, start_time, end_time, participants, price_per_person,
             total_price, status, notes, transport_from)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $statement->execute([
            $userId, (int) $data['tripId'], $scheduleId, $sessionId, $data['name'], strtolower($data['email']),
            $data['whatsapp'], $data['tripType'] ?? 'open', $data['experienceType'] ?? 'cave',
            $data['selectedDate'] ?? null, $data['startTime'] ?? null, $data['endTime'] ?? null,
            (int) $data['participants'], (float) ($data['pricePerPerson'] ?? $data['hargaPerOrang'] ?? 0),
            (float) ($data['totalPrice'] ?? $data['totalHarga'] ?? 0), 'Menunggu Approval',
            $data['notes'] ?? null, $data['transportFrom'] ?? null,
        ]);
        $bookingId = (int) $pdo->lastInsertId();

        $participantStatement = $pdo->prepare(
            'INSERT INTO booking_participants (booking_id, name, address, age, gender, health_notes) VALUES (?,?,?,?,?,?)'
        );
        foreach (($data['participantDetails'] ?? []) as $participant) {
            $participantStatement->execute([
                $bookingId, $participant['name'] ?? $data['name'], $participant['address'] ?? null,
                nullableInt($participant['age'] ?? null), $participant['gender'] ?? null, $participant['healthNotes'] ?? null,
            ]);
        }

        $addonStatement = $pdo->prepare('INSERT INTO booking_addons (booking_id, addon_id, quantity, price) VALUES (?,?,1,0)');
        foreach (array_unique((array) ($data['addons'] ?? [])) as $addonId) {
            $addonStatement->execute([$bookingId, $addonId]);
        }

        $pdo->commit();
        $bookingStatement = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $bookingStatement->execute([$bookingId]);
        jsonSuccess(mapBooking($pdo, $bookingStatement->fetch()), 201);
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
});
