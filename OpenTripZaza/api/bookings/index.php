<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $view = trim((string) ($_GET['view'] ?? ''));
    if ($view === '') {
        $view = ($_GET['archived'] ?? '') === '1' ? 'archived' : 'active';
    }
    if (!in_array($view, ['active', 'history', 'archived', 'all'], true)) {
        throw new InvalidArgumentException('Filter booking tidak valid.');
    }
    $endExpression = "TIMESTAMP(COALESCE(selected_date, DATE(created_at)), COALESCE(end_time, '23:59:59'))";
    $where = match ($view) {
        'active' => "{$endExpression} > NOW()",
        'history' => "{$endExpression} <= NOW()",
        'archived' => "archived_at IS NOT NULL OR {$endExpression} < NOW()",
        default => '1=1',
    };
    $rows = $pdo->query("SELECT * FROM bookings WHERE {$where} ORDER BY id DESC")->fetchAll();
    jsonSuccess(mapBookings($pdo, $rows));
});
