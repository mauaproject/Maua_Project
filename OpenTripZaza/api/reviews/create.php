<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['tripName', 'rating', 'content']);
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user || ($user['role'] ?? '') !== 'customer') {
        jsonError('Akses customer diperlukan.', 403);
    }
    $tripName = cleanReviewTripLabel($data['tripName']);
    $rating = (int) $data['rating'];
    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating harus antara 1 sampai 5.');
    }
    $content = cleanReviewContent($data['content']);

    $insert = $pdo->prepare(
        "INSERT INTO reviews
         (user_id, booking_id, trip_id, trip_label, reviewer_name, reviewer_email, rating, content, status)
         VALUES (?,NULL,NULL,?,?,?,?,?,'approved')"
    );
    $insert->execute([
        (int) $user['id'],
        $tripName,
        trim((string) $user['name']),
        strtolower(trim((string) $user['email'])),
        $rating,
        $content,
    ]);
    $id = (int) $pdo->lastInsertId();
    $result = $pdo->prepare(
        'SELECT r.*, COALESCE(t.name, r.trip_label) trip_name FROM reviews r LEFT JOIN trips t ON t.id=r.trip_id WHERE r.id=?'
    );
    $result->execute([$id]);
    jsonSuccess(mapReview($result->fetch()), 201);
});
