<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    expireUnpaidReschedules($pdo);
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user || !in_array($user['role'] ?? '', ['admin', 'customer'], true)) {
        jsonError('Akses tidak diizinkan.', 403);
    }
    $sql = rescheduleSelectSql();
    if ($user['role'] === 'customer') {
        $statement = $pdo->prepare($sql . ' WHERE b.user_id = ? ORDER BY r.id DESC');
        $statement->execute([(int) $user['id']]);
    } else {
        $statement = $pdo->query($sql . " ORDER BY FIELD(r.status, 'pending','awaiting_payment','approved','rejected','cancelled','expired'), r.id DESC");
    }
    jsonSuccess(mapRescheduleRows($statement->fetchAll()));
});
