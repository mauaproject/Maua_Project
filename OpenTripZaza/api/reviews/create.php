<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['userId', 'bookingId', 'email', 'rating', 'content']);
    $email = strtolower(trim((string) $data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $rating = (int) $data['rating'];
    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating harus antara 1 sampai 5.');
    }
    $content = cleanReviewContent($data['content']);

    $statement = $pdo->prepare(
        "SELECT b.id, b.trip_id, b.user_id, u.name, u.email
         FROM bookings b
         INNER JOIN users u ON u.id=b.user_id
         WHERE b.id=? AND b.user_id=? AND b.customer_email=? AND u.email=?
           AND b.status IN ('Disetujui','Selesai')
         LIMIT 1"
    );
    $statement->execute([(int) $data['bookingId'], (int) $data['userId'], $email, $email]);
    $booking = $statement->fetch();
    if (!$booking) {
        throw new InvalidArgumentException('Booking tidak valid atau belum disetujui.');
    }
    $exists = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE booking_id=?');
    $exists->execute([(int) $booking['id']]);
    if ((int) $exists->fetchColumn() > 0) {
        throw new InvalidArgumentException('Booking ini sudah memiliki review.');
    }

    $insert = $pdo->prepare(
        "INSERT INTO reviews
         (user_id, booking_id, trip_id, reviewer_name, reviewer_email, rating, content, status)
         VALUES (?,?,?,?,?,?,?,'approved')"
    );
    $insert->execute([
        (int) $booking['user_id'],
        (int) $booking['id'],
        (int) $booking['trip_id'],
        trim((string) $booking['name']),
        $email,
        $rating,
        $content,
    ]);
    $id = (int) $pdo->lastInsertId();
    $result = $pdo->prepare(
        'SELECT r.*, t.name trip_name FROM reviews r INNER JOIN trips t ON t.id=r.trip_id WHERE r.id=?'
    );
    $result->execute([$id]);
    jsonSuccess(mapReview($result->fetch()), 201);
});
