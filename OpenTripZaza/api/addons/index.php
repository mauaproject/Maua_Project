<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $rows = $pdo->query("SELECT id, label, worker_title, description, task, status FROM addons WHERE status = 'active' ORDER BY id")->fetchAll();
    jsonSuccess(array_map(static fn(array $item): array => [
        'id' => $item['id'],
        'label' => $item['label'],
        'workerTitle' => $item['worker_title'],
        'description' => $item['description'],
        'task' => $item['task'],
        'status' => $item['status'],
    ], $rows));
});
