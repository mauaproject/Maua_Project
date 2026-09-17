<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $user = requireRescheduleUser($pdo, 'customer');
    expireUnpaidReschedules($pdo);
    $data = jsonInput();
    requiredFields($data, ['id']);
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "SELECT r.*, b.trip_id FROM booking_reschedule_requests r
             INNER JOIN bookings b ON b.id = r.booking_id
             WHERE r.id = ? AND b.user_id = ? FOR UPDATE"
        );
        $statement->execute([(int) $data['id'], (int) $user['id']]);
        $request = $statement->fetch();
        if (!$request || !in_array($request['status'], ['awaiting_payment', 'pending'], true)) {
            throw new InvalidArgumentException('Pengajuan tidak ditemukan atau sudah diproses admin.');
        }
        if ($request['status'] === 'pending' && (float) $request['fee_amount'] > 0) {
            throw new InvalidArgumentException('Pengajuan yang sudah dibayar hanya dapat dibatalkan melalui admin WhatsApp.');
        }
        $pdo->prepare("UPDATE booking_reschedule_requests SET status = 'cancelled' WHERE id = ?")
            ->execute([(int) $data['id']]);
        if ($request['requested_schedule_id'] !== null) {
            syncOpenTripAvailability($pdo, (int) $request['requested_schedule_id'], (int) $request['trip_id']);
        }
        $pdo->commit();
        jsonSuccess(['id' => (int) $data['id'], 'status' => 'cancelled']);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
