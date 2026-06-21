<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $showAll = ($_GET['all'] ?? '') === '1';
    $summary = ($_GET['summary'] ?? '') === '1';
    $sql = $summary
        ? 'SELECT id, name, trip_type, experience_type, status, destination_id, destination_en,
                  price, quota, slots, min_participants, max_participants,
                  available_start_date, available_end_date, private_booking_mode
           FROM trips'
        : 'SELECT * FROM trips';
    if (!$showAll) {
        $sql .= " WHERE status IN ('Tersedia', 'Penuh')";
    }
    $sql .= ' ORDER BY id';
    $rows = $pdo->query($sql)->fetchAll();
    $trips = $summary
        ? mapTripSummaries($pdo, $rows)
        : array_map(fn(array $trip): array => mapTrip($pdo, $trip), $rows);
    jsonSuccess($trips);
});
