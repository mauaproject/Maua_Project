<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $admin = requireRescheduleUser($pdo, 'admin');
    $data = jsonInput();
    requiredFields($data, ['id', 'decision']);
    $decision = trim((string) $data['decision']);
    if (!in_array($decision, ['approved', 'rejected'], true)) {
        throw new InvalidArgumentException('Keputusan reschedule tidak valid.');
    }
    $adminNote = trim((string) ($data['adminNote'] ?? ''));
    if (rescheduleTextLength($adminNote) > 1000) {
        throw new InvalidArgumentException('Catatan admin maksimal 1000 karakter.');
    }

    $pdo->beginTransaction();
    try {
        $requestStatement = $pdo->prepare('SELECT * FROM booking_reschedule_requests WHERE id = ? FOR UPDATE');
        $requestStatement->execute([(int) $data['id']]);
        $request = $requestStatement->fetch();
        if (!$request) {
            $pdo->rollBack();
            jsonError('Pengajuan reschedule tidak ditemukan.', 404);
        }
        if ($request['status'] !== 'pending') {
            throw new InvalidArgumentException('Pengajuan ini sudah diproses.');
        }
        $bookingStatement = $pdo->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE');
        $bookingStatement->execute([(int) $request['booking_id']]);
        $booking = $bookingStatement->fetch();
        if (!$booking) {
            $pdo->rollBack();
            jsonError('Booking tidak ditemukan.', 404);
        }

        if ($decision === 'approved') {
            if (empty($booking['selected_date']) || $booking['status'] !== 'Disetujui' || scheduledEndAt((string) $booking['selected_date'], $booking['end_time']) <= appNow()) {
                throw new InvalidArgumentException('Booking sudah tidak aktif atau statusnya bukan Disetujui.');
            }
            $oldScheduleMatches = nullableInt($request['old_schedule_id']) === nullableInt($booking['schedule_id']);
            $oldSessionMatches = nullableInt($request['old_session_id']) === nullableInt($booking['session_id']);
            if (!$oldScheduleMatches || !$oldSessionMatches || $request['old_selected_date'] !== $booking['selected_date']) {
                throw new InvalidArgumentException('Jadwal booking telah berubah setelah pengajuan dibuat. Muat ulang data sebelum memproses.');
            }
            if ($booking['trip_type'] === 'open') {
                $targetId = (int) $request['requested_schedule_id'];
                $targetStatement = $pdo->prepare('SELECT * FROM trip_schedules WHERE id = ? AND trip_id = ? FOR UPDATE');
                $targetStatement->execute([$targetId, (int) $booking['trip_id']]);
                $target = $targetStatement->fetch();
                if (!$target) {
                    throw new InvalidArgumentException('Jadwal tujuan tidak ditemukan.');
                }
                $remaining = (int) $target['quota'] - getOpenTripReservedParticipants($pdo, $targetId);
                if ($target['status'] !== 'active' || !empty($target['archived_at']) || scheduleLifecycleStatus($target) !== 'upcoming' || $remaining < (int) $booking['participants']) {
                    throw new InvalidArgumentException('Jadwal tujuan sudah tidak tersedia atau slotnya tidak mencukupi.');
                }
                $oldScheduleId = nullableInt($booking['schedule_id']);
                $pdo->prepare(
                    'UPDATE bookings SET schedule_id = ?, session_id = NULL, selected_date = ?, visible_until = DATE_ADD(?, INTERVAL 1 DAY),
                     archived_at = NULL, start_time = ?, end_time = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                )->execute([$targetId, $target['schedule_date'], $target['schedule_date'], $target['start_time'], $target['end_time'], (int) $booking['id']]);
                if ($oldScheduleId !== null && $oldScheduleId !== $targetId) {
                    syncOpenTripAvailability($pdo, $oldScheduleId, (int) $booking['trip_id']);
                }
                syncOpenTripAvailability($pdo, $targetId, (int) $booking['trip_id']);
            } else {
                $targetSessionId = (int) $request['requested_session_id'];
                $targetDate = $request['requested_date'];
                $tripStatement = $pdo->prepare('SELECT available_start_date, available_end_date, private_booking_mode FROM trips WHERE id = ? FOR UPDATE');
                $tripStatement->execute([(int) $booking['trip_id']]);
                $trip = $tripStatement->fetch();
                $sessionStatement = $pdo->prepare("SELECT * FROM trip_sessions WHERE id = ? AND trip_id = ? AND status = 'active' FOR UPDATE");
                $sessionStatement->execute([$targetSessionId, (int) $booking['trip_id']]);
                $session = $sessionStatement->fetch();
                if (!$session
                    || (!empty($trip['available_start_date']) && $targetDate < $trip['available_start_date'])
                    || (!empty($trip['available_end_date']) && $targetDate > $trip['available_end_date'])
                    || scheduledEndAt($targetDate, $session['end_time']) <= appNow()) {
                    throw new InvalidArgumentException('Jadwal tujuan sudah tidak tersedia.');
                }
                if (($trip['private_booking_mode'] ?? 'exclusive') !== 'shared') {
                    $collision = $pdo->prepare(
                        "SELECT id FROM bookings WHERE trip_id = ? AND session_id = ? AND selected_date = ? AND id <> ?
                         AND status IN ('Menunggu Approval','Disetujui','Selesai') FOR UPDATE"
                    );
                    $collision->execute([(int) $booking['trip_id'], $targetSessionId, $targetDate, (int) $booking['id']]);
                    if ($collision->fetch()) {
                        throw new InvalidArgumentException('Sesi tujuan sudah dipesan customer lain.');
                    }
                }
                $pdo->prepare(
                    'UPDATE bookings SET schedule_id = NULL, session_id = ?, selected_date = ?, visible_until = DATE_ADD(?, INTERVAL 1 DAY),
                     archived_at = NULL, start_time = ?, end_time = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                )->execute([$targetSessionId, $targetDate, $targetDate, $session['start_time'], $session['end_time'], (int) $booking['id']]);
            }
            // Jadwal berubah, sehingga reminder H-7/H-1 lama tidak boleh menghalangi reminder untuk jadwal baru.
            $pdo->prepare("DELETE FROM reminder_logs WHERE booking_id = ? AND reminder_type IN ('H7','H1')")
                ->execute([(int) $booking['id']]);
        }

        $pdo->prepare(
            'UPDATE booking_reschedule_requests
             SET status = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW(), updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        )->execute([$decision, $adminNote !== '' ? $adminNote : null, (int) $admin['id'], (int) $request['id']]);
        $pdo->commit();
        jsonSuccess(['id' => (int) $request['id'], 'bookingId' => (int) $booking['id'], 'status' => $decision]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
