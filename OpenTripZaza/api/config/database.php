<?php
declare(strict_types=1);

function loadLocalEnvironment(): void
{
    $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
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
    return $pdo;
}
