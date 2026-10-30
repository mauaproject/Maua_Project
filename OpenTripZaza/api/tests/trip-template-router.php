<?php
declare(strict_types=1);
// Disposable local test server only, never a production router.
if (PHP_SAPI !== 'cli-server' || getenv('DB_NAME') !== 'maua_template_management_test'
    || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_PORT') !== '33079') {
    http_response_code(404);
    exit;
}
$root = dirname(__DIR__, 2);
$path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($path === '/__template_test_health') {
    require_once dirname(__DIR__) . '/config/database.php';
    header('Content-Type: application/json');
    echo json_encode(['database' => database()->query('SELECT DATABASE()')->fetchColumn()]);
    return true;
}
if (str_starts_with($path, '/api/') || str_starts_with($path, '/uploads/')) return false;
$file = realpath($root . '/dist' . $path);
$dist = realpath($root . '/dist');
if ($file && $dist && str_starts_with($file, $dist . DIRECTORY_SEPARATOR) && is_file($file)) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if (!in_array($extension, ['js', 'css', 'svg', 'webp', 'png', 'jpg', 'ico', 'json'], true)) {
        http_response_code(404);
        return true;
    }
    header('Content-Type: ' . (['js' => 'application/javascript', 'css' => 'text/css', 'svg' => 'image/svg+xml', 'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'ico' => 'image/x-icon', 'json' => 'application/json'][$extension]));
    readfile($file);
    return true;
}
header('Content-Type: text/html; charset=utf-8');
readfile($root . '/dist/index.html');
return true;
