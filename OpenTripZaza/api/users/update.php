<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'name', 'email']);

    $id = (int) $data['id'];
    $name = trim((string) $data['name']);
    $email = strtolower(trim((string) $data['email']));
    $password = (string) ($data['password'] ?? '');

    if ($id <= 0 || $name === '') {
        throw new InvalidArgumentException('Data akun tim tidak valid.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    if ($password !== '' && strlen($password) < 6) {
        throw new InvalidArgumentException('Password minimal 6 karakter.');
    }

    $existing = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'worker' LIMIT 1");
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        jsonError('Akun tim tidak ditemukan.', 404);
    }

    try {
        if ($password !== '') {
            $statement = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, password_hash = ?, email_verified = 1, email_verified_at = COALESCE(email_verified_at, NOW())
                 WHERE id = ? AND role = ?'
            );
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $id, 'worker']);
        } else {
            $statement = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, email_verified = 1, email_verified_at = COALESCE(email_verified_at, NOW())
                 WHERE id = ? AND role = ?'
            );
            $statement->execute([$name, $email, $id, 'worker']);
        }
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            jsonError('Email sudah terdaftar.', 409);
        }
        throw $exception;
    }

    jsonSuccess([
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'role' => 'pekerja',
    ]);
});
