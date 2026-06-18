<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $sql = "SELECT wt.*, COALESCE(wt.addon_name, ta.name, a.label) addon_label, u.name worker_name,
            (SELECT COUNT(*) FROM worker_tasks scope_tasks
             WHERE scope_tasks.booking_id = wt.booking_id
               AND COALESCE(scope_tasks.trip_addon_id, 0) = COALESCE(wt.trip_addon_id, 0)
               AND COALESCE(scope_tasks.addon_id, '') = COALESCE(wt.addon_id, '')) total_workers
            FROM worker_tasks wt
            LEFT JOIN addons a ON a.id = wt.addon_id
            LEFT JOIN trip_addons ta ON ta.id = wt.trip_addon_id
            LEFT JOIN users u ON u.id = wt.worker_id";
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
