<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $email = trim((string) ($_GET['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $view = trim((string) ($_GET['view'] ?? ''));
    if ($view === '') {
        $view = ($_GET['archived'] ?? '') === '1' ? 'history' : 'active';
    }
    if (!in_array($view, ['active', 'history', 'all'], true)) {
        throw new InvalidArgumentException('Filter booking customer tidak valid.');
    }
    $endExpression = "TIMESTAMP(COALESCE(selected_date, DATE(created_at)), COALESCE(end_time, '23:59:59'))";
    $retentionWhere = match ($view) {
        'active' => "{$endExpression} > NOW()",
        'history' => "{$endExpression} <= NOW()",
        default => '1=1',
    };
    $statement = $pdo->prepare("SELECT * FROM bookings WHERE customer_email = ? AND {$retentionWhere} ORDER BY id DESC");
    $statement->execute([$email]);
    jsonSuccess(mapBookings($pdo, $statement->fetchAll()));
});
