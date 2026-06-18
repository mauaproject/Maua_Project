<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $sql = "SELECT wt.*, a.label addon_label, u.name worker_name
            FROM worker_tasks wt
            LEFT JOIN addons a ON a.id = wt.addon_id
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
