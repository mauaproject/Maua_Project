<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['tripId', 'rating', 'content']);
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user || ($user['role'] ?? '') !== 'customer') {
        jsonError('Akses customer diperlukan.', 403);
    }
    $tripId = (int) $data['tripId'];
    if ($tripId <= 0) {
        throw new InvalidArgumentException('Trip tidak valid.');
    }
    $rating = (int) $data['rating'];
    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating harus antara 1 sampai 5.');
    }
    $content = cleanReviewContent($data['content']);

    $tripStatement = $pdo->prepare('SELECT id FROM trips WHERE id=? LIMIT 1');
    $tripStatement->execute([$tripId]);
    if (!$tripStatement->fetch()) {
        throw new InvalidArgumentException('Trip tidak ditemukan.');
    }

    $insert = $pdo->prepare(
        "INSERT INTO reviews
         (user_id, booking_id, trip_id, reviewer_name, reviewer_email, rating, content, status)
         VALUES (?,NULL,?,?,?,?,?,'approved')"
    );
    $insert->execute([
        (int) $user['id'],
        $tripId,
        trim((string) $user['name']),
        strtolower(trim((string) $user['email'])),
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
