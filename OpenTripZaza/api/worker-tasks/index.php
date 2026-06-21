<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $sql = "SELECT wt.*, COALESCE(wt.addon_name, ta.name, a.label) addon_label, u.name worker_name,
            COALESCE(scope_counts.total_workers, 1) total_workers
            FROM worker_tasks wt
            LEFT JOIN addons a ON a.id = wt.addon_id
            LEFT JOIN trip_addons ta ON ta.id = wt.trip_addon_id
            LEFT JOIN users u ON u.id = wt.worker_id
            LEFT JOIN (
                SELECT booking_id, COALESCE(trip_addon_id, 0) trip_addon_key,
                       COALESCE(addon_id, '') addon_key, COUNT(*) total_workers
                FROM worker_tasks
                GROUP BY booking_id, COALESCE(trip_addon_id, 0), COALESCE(addon_id, '')
            ) scope_counts
              ON scope_counts.booking_id = wt.booking_id
             AND scope_counts.trip_addon_key = COALESCE(wt.trip_addon_id, 0)
             AND scope_counts.addon_key = COALESCE(wt.addon_id, '')";
    $params = [];
    if (!empty($_GET['worker_email'])) {
        $sql .= ' WHERE u.email = ?';
        $params[] = strtolower(trim((string) $_GET['worker_email']));
    }
    $sql .= ' ORDER BY wt.id';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    jsonSuccess(array_map('mapWorkerTask', $statement->fetchAll()));
});
