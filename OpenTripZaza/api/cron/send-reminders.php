<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/reminders.php';

loadLocalEnvironment();

if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    $configuredSecret = (string) getenv('CRON_SECRET');
    $providedSecret = (string) ($_GET['token'] ?? '');
    if ($configuredSecret === '' || $providedSecret === '' || !hash_equals($configuredSecret, $providedSecret)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses cron ditolak.']);
        exit;
    }
}

try {
    $result = runDailyReminders(database());
    $payload = ['success' => $result['failed'] === 0, 'data' => $result];
    if (PHP_SAPI === 'cli') {
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    exit($result['failed'] > 0 ? 1 : 0);
} catch (Throwable $exception) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
    }
    echo json_encode([
        'success' => false,
        'message' => 'Cron reminder gagal dijalankan.',
        'error' => getenv('APP_DEBUG') === 'true' ? $exception->getMessage() : null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . (PHP_SAPI === 'cli' ? PHP_EOL : '');
    exit(1);
}
