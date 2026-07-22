<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $user = requireRescheduleUser($pdo, 'customer');
    $data = jsonInput();
    requiredFields($data, ['id']);
    $statement = $pdo->prepare(
        "UPDATE booking_reschedule_requests r
         INNER JOIN bookings b ON b.id = r.booking_id
         SET r.status = 'cancelled', r.updated_at = CURRENT_TIMESTAMP
         WHERE r.id = ? AND b.user_id = ? AND r.status = 'pending'"
    );
    $statement->execute([(int) $data['id'], (int) $user['id']]);
    if ($statement->rowCount() !== 1) {
        throw new InvalidArgumentException('Pengajuan tidak ditemukan atau sudah diproses admin.');
    }
    jsonSuccess(['id' => (int) $data['id'], 'status' => 'cancelled']);
});
