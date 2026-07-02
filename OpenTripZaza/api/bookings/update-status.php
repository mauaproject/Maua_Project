<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'status']);
    $allowed = ['Menunggu Approval', 'Disetujui', 'Ditolak', 'Dibatalkan', 'Expired', 'Selesai'];
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
        if ($booking['schedule_id']) {
            $scheduleStatement = $pdo->prepare('SELECT quota, booked_count FROM trip_schedules WHERE id = ? FOR UPDATE');
            $scheduleStatement->execute([(int) $booking['schedule_id']]);
            $schedule = $scheduleStatement->fetch();
            if (!$schedule) {
                throw new InvalidArgumentException('Jadwal trip tidak tersedia.');
            }
            if (
                !bookingHoldsOpenTripSlot($booking['status'])
                && bookingHoldsOpenTripSlot($data['status'])
                && ((int) $schedule['quota'] - max((int) $schedule['booked_count'], getOpenTripReservedParticipants($pdo, (int) $booking['schedule_id']))) < (int) $booking['participants']
            ) {
                throw new InvalidArgumentException('Slot jadwal tidak mencukupi untuk mengaktifkan booking ini.');
            }
        }
        $paymentStatus = match ($data['status']) {
            'Disetujui', 'Selesai' => 'verified',
            'Ditolak', 'Dibatalkan', 'Expired' => 'rejected',
            default => 'waiting_verification',
        };
        $pdo->prepare(
            'UPDATE bookings SET status = ?, payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
        )->execute([$data['status'], $paymentStatus, $bookingId]);
        $pdo->prepare('UPDATE payments SET payment_status = ? WHERE booking_id = ?')
            ->execute([$paymentStatus, $bookingId]);

        if ($booking['schedule_id']) {
            syncOpenTripAvailability($pdo, (int) $booking['schedule_id'], (int) $booking['trip_id']);
        }

        if (in_array($data['status'], ['Disetujui', 'Selesai'], true)) {
            $bookingAddonNameSelect = tableHasColumn($pdo, 'booking_addons', 'addon_name') ? 'ba.addon_name' : 'NULL';
            $bookingAddonActionSelect = tableHasColumn($pdo, 'booking_addons', 'worker_action') ? 'ba.worker_action' : 'NULL';
            $addonsStatement = $pdo->prepare(
                "SELECT ba.addon_id, ba.trip_addon_id, ba.quantity,
                        COALESCE($bookingAddonNameSelect, ta.name, a.label, ba.addon_id) addon_name,
                        COALESCE($bookingAddonActionSelect, ta.worker_action, 'none') worker_action,
                        COALESCE(a.task, CONCAT('Kerjakan add-on ', COALESCE($bookingAddonNameSelect, ta.name, a.label, ba.addon_id), ' untuk booking ini.')) task
                 FROM booking_addons ba
                 LEFT JOIN trip_addons ta ON ta.id = ba.trip_addon_id
                 LEFT JOIN addons a ON a.id = ba.addon_id
                 WHERE ba.booking_id = ?"
            );
            $addonsStatement->execute([$bookingId]);
            $insertTask = $pdo->prepare(
                "INSERT INTO worker_tasks
                 (booking_id, trip_id, addon_id, trip_addon_id, addon_name, worker_action, slot, task, status)
                 SELECT ?,?,?,?,?,?,?,?,'Tersedia' FROM DUAL
                 WHERE NOT EXISTS (
                    SELECT 1 FROM worker_tasks
                    WHERE booking_id = ?
                      AND COALESCE(trip_addon_id, 0) = COALESCE(?, 0)
                      AND COALESCE(addon_id, '') = COALESCE(?, '')
                      AND slot = ?
                 )"
            );
            foreach ($addonsStatement->fetchAll() as $addon) {
                $quantity = max(1, (int) $addon['quantity']);
                for ($slot = 1; $slot <= $quantity; $slot++) {
                    $task = $addon['task'];
                    if ($addon['addon_id'] === 'transport' && $booking['transport_from']) {
                        $task .= ' Titik jemput: ' . $booking['transport_from'] . '.';
                    }
                    $insertTask->execute([
                        $bookingId, (int) $booking['trip_id'], $addon['addon_id'], nullableInt($addon['trip_addon_id']),
                        $addon['addon_name'], $addon['worker_action'], $slot, $task,
                        $bookingId, nullableInt($addon['trip_addon_id']), $addon['addon_id'], $slot,
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
