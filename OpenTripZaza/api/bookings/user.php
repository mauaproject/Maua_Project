<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $email = trim((string) ($_GET['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $showArchived = ($_GET['archived'] ?? '') === '1';
    $retentionWhere = $showArchived
        ? '(archived_at IS NOT NULL OR (visible_until IS NOT NULL AND visible_until < CURDATE()))'
        : 'archived_at IS NULL AND (visible_until IS NULL OR visible_until >= CURDATE())';
    $statement = $pdo->prepare("SELECT * FROM bookings WHERE customer_email = ? AND {$retentionWhere} ORDER BY id DESC");
    $statement->execute([$email]);
    jsonSuccess(array_map(fn(array $row): array => mapBooking($pdo, $row), $statement->fetchAll()));
});
