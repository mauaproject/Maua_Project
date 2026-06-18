<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $rows = $pdo->query('SELECT * FROM bookings ORDER BY id DESC')->fetchAll();
    jsonSuccess(array_map(fn(array $row): array => mapBooking($pdo, $row), $rows));
});
