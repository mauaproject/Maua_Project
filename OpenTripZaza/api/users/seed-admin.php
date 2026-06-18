<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $count = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($count > 0) {
        jsonSuccess(['seeded' => false, 'message' => 'Akun admin sudah tersedia.']);
    }
    $email = getenv('ADMIN_EMAIL') ?: '';
    $password = getenv('ADMIN_PASSWORD') ?: '';
    $name = getenv('ADMIN_NAME') ?: 'Admin MAUA';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        throw new InvalidArgumentException('Isi ADMIN_EMAIL dan ADMIN_PASSWORD minimal 8 karakter di environment terlebih dahulu.');
    }
    $statement = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,'admin')");
    $statement->execute([$name, strtolower($email), password_hash($password, PASSWORD_DEFAULT)]);
    jsonSuccess(['seeded' => true, 'id' => (int) $pdo->lastInsertId(), 'email' => strtolower($email)], 201);
});
