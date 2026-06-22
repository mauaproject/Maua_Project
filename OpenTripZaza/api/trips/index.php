<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $showAll = ($_GET['all'] ?? '') === '1';
    $summary = ($_GET['summary'] ?? '') === '1';
    $customerView = !$showAll;
    $sql = $summary
        ? 'SELECT id, name, trip_type, experience_type, status, destination_id, destination_en,
                  price, quota, slots, min_participants, max_participants,
                  available_start_date, available_end_date, private_booking_mode
           FROM trips t'
        : 'SELECT t.* FROM trips t';
    if ($customerView) {
        $sql .= " WHERE t.status = 'Tersedia'
          AND (
            (
              t.trip_type = 'open'
              AND EXISTS (
                SELECT 1
                FROM trip_schedules ts
                WHERE ts.trip_id = t.id
                  AND ts.status = 'active'
                  AND ts.quota > ts.booked_count
                  AND TIMESTAMP(ts.schedule_date, COALESCE(ts.end_time, '23:59:59')) > NOW()
              )
            )
            OR
            (
              t.trip_type = 'private'
              AND t.available_end_date IS NOT NULL
              AND EXISTS (
                SELECT 1
                FROM trip_sessions tss
                WHERE tss.trip_id = t.id
                  AND tss.status = 'active'
                  AND TIMESTAMP(t.available_end_date, COALESCE(tss.end_time, '23:59:59')) > NOW()
              )
            )
          )";
    }
    $sql .= ' ORDER BY t.id';
    $rows = $pdo->query($sql)->fetchAll();
    $trips = $summary
        ? mapTripSummaries($pdo, $rows, $customerView)
        : array_map(fn(array $trip): array => mapTrip($pdo, $trip, $customerView), $rows);
    jsonSuccess($trips);
});
