<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $showAll = ($_GET['all'] ?? '') === '1';
    $sql = 'SELECT * FROM trips';
    if (!$showAll) {
        $sql .= " WHERE status IN ('Tersedia', 'Penuh')";
    }
    $sql .= ' ORDER BY id';
    $trips = array_map(fn(array $trip): array => mapTrip($pdo, $trip), $pdo->query($sql)->fetchAll());
    jsonSuccess($trips);
});
