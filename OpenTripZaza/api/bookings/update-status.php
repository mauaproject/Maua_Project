<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'status']);
    $allowed = ['Menunggu Approval', 'Disetujui', 'Ditolak', 'Selesai'];
    if (!in_array($data['status'], $allowed, true)) {
        throw new InvalidArgumentException('Status booking tidak valid.');
    }
    $bookingId = (int) $data['id'];
    $pdo->beginTransaction();
    try {
        $bookingStatement = $pdo->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE');
        $bookingStatement->execute([$bookingId]);
        $booking = $bookingStatement->fetch();
        if (!$booking) {
            jsonError('Booking tidak ditemukan.', 404);
        }
        $pdo->prepare('UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$data['status'], $bookingId]);

        if ($booking['schedule_id']) {
            $countStatement = $pdo->prepare(
                "SELECT COALESCE(SUM(participants), 0) FROM bookings
                 WHERE schedule_id = ? AND status IN ('Disetujui','Selesai')"
            );
            $countStatement->execute([(int) $booking['schedule_id']]);
            $bookedCount = (int) $countStatement->fetchColumn();
            $scheduleStatement = $pdo->prepare(
                "UPDATE trip_schedules SET booked_count = ?,
                 status = CASE WHEN status = 'inactive' THEN 'inactive' WHEN quota <= ? THEN 'full' ELSE 'active' END
                 WHERE id = ?"
            );
            $scheduleStatement->execute([$bookedCount, $bookedCount, (int) $booking['schedule_id']]);
            $totalsStatement = $pdo->prepare('SELECT COALESCE(SUM(quota),0) quota, COALESCE(SUM(GREATEST(quota-booked_count,0)),0) slots FROM trip_schedules WHERE trip_id = ?');
            $totalsStatement->execute([(int) $booking['trip_id']]);
            $totals = $totalsStatement->fetch();
            $pdo->prepare(
                "UPDATE trips SET quota=?, slots=?, status=CASE WHEN status IN ('Ditutup','Selesai') THEN status WHEN ? <= 0 THEN 'Penuh' ELSE 'Tersedia' END WHERE id=?"
            )->execute([(int) $totals['quota'], (int) $totals['slots'], (int) $totals['slots'], (int) $booking['trip_id']]);
        }

        if (in_array($data['status'], ['Disetujui', 'Selesai'], true)) {
            $addonsStatement = $pdo->prepare(
                'SELECT ba.addon_id, ba.quantity, a.task FROM booking_addons ba JOIN addons a ON a.id = ba.addon_id WHERE ba.booking_id = ?'
            );
            $addonsStatement->execute([$bookingId]);
            $insertTask = $pdo->prepare(
                "INSERT INTO worker_tasks (booking_id, trip_id, addon_id, slot, total_workers, task, status)
                 SELECT ?,?,?,?,?,?,'Tersedia' FROM DUAL
                 WHERE NOT EXISTS (SELECT 1 FROM worker_tasks WHERE booking_id = ? AND addon_id = ? AND slot = ?)"
            );
            foreach ($addonsStatement->fetchAll() as $addon) {
                $quantity = max(1, (int) $addon['quantity']);
                for ($slot = 1; $slot <= $quantity; $slot++) {
                    $task = $addon['task'];
                    if ($addon['addon_id'] === 'transport' && $booking['transport_from']) {
                        $task .= ' Titik jemput: ' . $booking['transport_from'] . '.';
                    }
                    $insertTask->execute([
                        $bookingId, (int) $booking['trip_id'], $addon['addon_id'], $slot, $quantity, $task,
                        $bookingId, $addon['addon_id'], $slot,
                    ]);
                }
            }
        } else {
            $pdo->prepare("DELETE FROM worker_tasks WHERE booking_id = ? AND worker_id IS NULL AND status = 'Tersedia'")->execute([$bookingId]);
        }

        $pdo->commit();
        jsonSuccess(['id' => $bookingId, 'status' => $data['status']]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
