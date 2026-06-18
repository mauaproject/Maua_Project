<?php
declare(strict_types=1);

function applyCors(): void
{
    $allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: '*';
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function jsonSuccess(mixed $data = null, int $status = 200): never
{
    http_response_code($status);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $status = 400, ?array $details = null): never
{
    http_response_code($status);
    $payload = ['success' => false, 'message' => $message];
    if ($details !== null && (getenv('APP_DEBUG') === 'true')) {
        $payload['details'] = $details;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireMethod(string|array $methods): void
{
    $allowed = array_map('strtoupper', (array) $methods);
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        jsonError('Method request tidak diizinkan.', 405);
    }
}

function jsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonError('Payload JSON tidak valid.', 422);
    }
    return $data;
}

applyCors();
