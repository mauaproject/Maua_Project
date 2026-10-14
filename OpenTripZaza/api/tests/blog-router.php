<?php
declare(strict_types=1);
// Local test server only. Never use this router for hosting.
if (PHP_SAPI !== 'cli-server' || getenv('DB_NAME') !== 'maua_blog_test' || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_PORT') !== '33077') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
$path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($path === '/__blog_test_health') {
    require_once dirname(__DIR__) . '/config/database.php';
    header('Content-Type: application/json');
    echo json_encode(['database' => database()->query('SELECT DATABASE()')->fetchColumn()]);
    return true;
}
if (str_starts_with($path, '/api/') || str_starts_with($path, '/uploads/')) return false;
if ($path === '/sitemap.xml') { require $root . '/dist/sitemap.php'; return true; }
$file = realpath($root . '/dist' . $path);
$dist = realpath($root . '/dist');
if ($file && $dist && str_starts_with($file, $dist . DIRECTORY_SEPARATOR) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    $type = ['js' => 'application/javascript', 'css' => 'text/css', 'svg' => 'image/svg+xml', 'webp' => 'image/webp', 'json' => 'application/json'][pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream';
    header('Content-Type: ' . $type);
    readfile($file);
    return true;
}
require $root . '/dist/seo-render.php';
return true;
