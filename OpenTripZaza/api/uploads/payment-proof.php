<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if (!$bookingId || !isset($_FILES['proof'])) {
        throw new InvalidArgumentException('booking_id dan file proof wajib diisi.');
    }
    $exists = $pdo->prepare('SELECT id FROM bookings WHERE id = ?');
    $exists->execute([$bookingId]);
    if (!$exists->fetch()) {
        jsonError('Booking tidak ditemukan.', 404);
    }
    $stored = storeUploadedImage($_FILES['proof'], 'payment-proofs');
    $method = trim((string) ($_POST['payment_method'] ?? 'transfer'));
    $amount = (float) ($_POST['amount'] ?? 0);
    $statement = $pdo->prepare(
        "INSERT INTO payments (booking_id, amount, payment_method, payment_proof_url, payment_status, submitted_at)
         VALUES (?,?,?,?, 'submitted', NOW())"
    );
    $statement->execute([$bookingId, $amount, $method, $stored['path']]);
    jsonSuccess(['id' => (int) $pdo->lastInsertId(), 'bookingId' => $bookingId, 'url' => $stored['path']], 201);
});
