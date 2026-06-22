<?php
declare(strict_types=1);

function loadLocalEnvironment(): void
{
    $publicRoot = dirname(__DIR__, 2);
    $candidates = [
        dirname($publicRoot) . DIRECTORY_SEPARATOR . '.env',
        $publicRoot . DIRECTORY_SEPARATOR . '.env',
    ];
    $envFile = null;
    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_readable($candidate)) {
            $envFile = $candidate;
            break;
        }
    }
    if ($envFile === null) {
        return;
    }

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    loadLocalEnvironment();
    $timezone = trim((string) (getenv('APP_TIMEZONE') ?: 'Asia/Jakarta'));
    date_default_timezone_set($timezone);
    $localConfig = [];
    $localFile = __DIR__ . DIRECTORY_SEPARATOR . 'database.local.php';
    if (is_file($localFile)) {
        $loaded = require $localFile;
        $localConfig = is_array($loaded) ? $loaded : [];
    }

    $config = [
        'host' => getenv('DB_HOST') ?: ($localConfig['host'] ?? 'localhost'),
        'name' => getenv('DB_NAME') ?: ($localConfig['name'] ?? 'ISI_NAMA_DATABASE'),
        'user' => getenv('DB_USER') ?: ($localConfig['user'] ?? 'ISI_USER_DATABASE'),
        'password' => getenv('DB_PASSWORD') ?: ($localConfig['password'] ?? ''),
        'port' => getenv('DB_PORT') ?: ($localConfig['port'] ?? '3306'),
    ];

    if (str_starts_with($config['name'], 'ISI_') || str_starts_with($config['user'], 'ISI_')) {
        throw new RuntimeException('Konfigurasi database belum diisi.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['name']
    );

    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $offsetSeconds = (new DateTimeImmutable('now', new DateTimeZone($timezone)))->getOffset();
    $offsetSign = $offsetSeconds < 0 ? '-' : '+';
    $offsetSeconds = abs($offsetSeconds);
    $offset = sprintf('%s%02d:%02d', $offsetSign, intdiv($offsetSeconds, 3600), intdiv($offsetSeconds % 3600, 60));
    $pdo->exec("SET time_zone = " . $pdo->quote($offset));
    return $pdo;
}
