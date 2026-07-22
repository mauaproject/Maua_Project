<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $user = requireRescheduleUser($pdo, 'customer');
    $data = jsonInput();
    requiredFields($data, ['bookingId', 'reason']);
    if (!filter_var($data['adminContactConfirmed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        throw new InvalidArgumentException('Konfirmasi bahwa admin WhatsApp sudah dihubungi terlebih dahulu.');
    }
    $bookingId = (int) $data['bookingId'];
    $reason = trim((string) $data['reason']);
    if (rescheduleTextLength($reason) < 5 || rescheduleTextLength($reason) > 1000) {
        throw new InvalidArgumentException('Alasan reschedule harus berisi 5 sampai 1000 karakter.');
    }

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "SELECT b.*, t.available_start_date, t.available_end_date, t.private_booking_mode
             FROM bookings b INNER JOIN trips t ON t.id = b.trip_id
             WHERE b.id = ? AND b.user_id = ? FOR UPDATE"
        );
        $statement->execute([$bookingId, (int) $user['id']]);
        $booking = $statement->fetch();
        if (!$booking) {
            $pdo->rollBack();
            jsonError('Booking tidak ditemukan.', 404);
        }
        if (empty($booking['selected_date']) || $booking['status'] !== 'Disetujui' || scheduledEndAt((string) $booking['selected_date'], $booking['end_time']) <= appNow()) {
            throw new InvalidArgumentException('Hanya booking aktif yang sudah disetujui yang dapat di-reschedule.');
        }
        $pending = $pdo->prepare("SELECT id FROM booking_reschedule_requests WHERE booking_id = ? AND status = 'pending' FOR UPDATE");
        $pending->execute([$bookingId]);
        if ($pending->fetch()) {
            throw new InvalidArgumentException('Booking ini sudah memiliki pengajuan reschedule yang menunggu persetujuan.');
        }

        $targetScheduleId = null;
        $targetSessionId = null;
        $targetDate = '';
        $targetStart = null;
        $targetEnd = null;
        if ($booking['trip_type'] === 'open') {
            $targetScheduleId = (int) ($data['requestedScheduleId'] ?? 0);
            if (!$targetScheduleId) {
                throw new InvalidArgumentException('Pilih jadwal baru terlebih dahulu.');
            }
            $target = $pdo->prepare('SELECT * FROM trip_schedules WHERE id = ? AND trip_id = ? FOR UPDATE');
            $target->execute([$targetScheduleId, (int) $booking['trip_id']]);
            $schedule = $target->fetch();
            if (!$schedule || (int) $schedule['id'] === (int) $booking['schedule_id']) {
                throw new InvalidArgumentException('Jadwal baru tidak valid.');
            }
            $remaining = (int) $schedule['quota'] - getOpenTripReservedParticipants($pdo, $targetScheduleId);
            if ($schedule['status'] !== 'active' || !empty($schedule['archived_at']) || scheduleLifecycleStatus($schedule) !== 'upcoming' || $remaining < (int) $booking['participants']) {
                throw new InvalidArgumentException('Jadwal tujuan sudah tidak tersedia atau slotnya tidak mencukupi.');
            }
            $targetDate = $schedule['schedule_date'];
            $targetStart = $schedule['start_time'];
            $targetEnd = $schedule['end_time'];
        } else {
            $targetSessionId = (int) ($data['requestedSessionId'] ?? 0);
            $targetDate = trim((string) ($data['requestedDate'] ?? ''));
            if (!$targetSessionId || !validRescheduleDate($targetDate)) {
                throw new InvalidArgumentException('Tanggal dan sesi baru wajib dipilih.');
            }
            $sessionStatement = $pdo->prepare("SELECT * FROM trip_sessions WHERE id = ? AND trip_id = ? AND status = 'active' FOR UPDATE");
            $sessionStatement->execute([$targetSessionId, (int) $booking['trip_id']]);
            $session = $sessionStatement->fetch();
            if (!$session) {
                throw new InvalidArgumentException('Sesi tujuan tidak tersedia.');
            }
            if ((!empty($booking['available_start_date']) && $targetDate < $booking['available_start_date'])
                || (!empty($booking['available_end_date']) && $targetDate > $booking['available_end_date'])
                || scheduledEndAt($targetDate, $session['end_time']) <= appNow()) {
                throw new InvalidArgumentException('Tanggal tujuan tidak tersedia.');
            }
            if ($targetDate === $booking['selected_date'] && $targetSessionId === (int) $booking['session_id']) {
                throw new InvalidArgumentException('Jadwal baru harus berbeda dari jadwal saat ini.');
            }
            if (($booking['private_booking_mode'] ?? 'exclusive') !== 'shared') {
                $collision = $pdo->prepare(
                    "SELECT id FROM bookings WHERE trip_id = ? AND session_id = ? AND selected_date = ? AND id <> ?
                     AND status IN ('Menunggu Approval','Disetujui','Selesai') FOR UPDATE"
                );
                $collision->execute([(int) $booking['trip_id'], $targetSessionId, $targetDate, $bookingId]);
                if ($collision->fetch()) {
                    throw new InvalidArgumentException('Sesi pada tanggal tersebut sudah dipesan.');
                }
            }
            $targetStart = $session['start_time'];
            $targetEnd = $session['end_time'];
        }

        $insert = $pdo->prepare(
            "INSERT INTO booking_reschedule_requests
             (booking_id, old_schedule_id, old_session_id, old_selected_date, old_start_time, old_end_time,
              requested_schedule_id, requested_session_id, requested_date, requested_start_time, requested_end_time, reason)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $insert->execute([
            $bookingId, nullableInt($booking['schedule_id']), nullableInt($booking['session_id']),
            $booking['selected_date'], $booking['start_time'], $booking['end_time'],
            $targetScheduleId, $targetSessionId, $targetDate, $targetStart, $targetEnd, $reason,
        ]);
        $requestId = (int) $pdo->lastInsertId();
        $pdo->commit();

        $result = $pdo->prepare(rescheduleSelectSql() . ' WHERE r.id = ?');
        $result->execute([$requestId]);
        jsonSuccess(mapRescheduleRows($result->fetchAll())[0], 201);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
