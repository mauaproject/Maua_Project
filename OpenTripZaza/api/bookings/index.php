<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $showArchived = ($_GET['archived'] ?? '') === '1';
    $where = $showArchived
        ? 'archived_at IS NOT NULL OR (visible_until IS NOT NULL AND visible_until < CURDATE())'
        : 'archived_at IS NULL AND (visible_until IS NULL OR visible_until >= CURDATE())';
    $rows = $pdo->query("SELECT * FROM bookings WHERE {$where} ORDER BY id DESC")->fetchAll();
    jsonSuccess(array_map(fn(array $row): array => mapBooking($pdo, $row), $rows));
});
