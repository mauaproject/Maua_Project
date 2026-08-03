<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/password-reset.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['email']);
    $email = strtolower(trim((string) $data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }

    $genericResponse = [
        'message' => 'Jika email terdaftar sebagai customer, tautan reset akan dikirim melalui Gmail.',
    ];
    $statement = $pdo->prepare("SELECT id, name, email FROM users WHERE email=? AND role='customer' LIMIT 1");
    $statement->execute([$email]);
    $user = $statement->fetch();
    if (!$user) {
        jsonSuccess($genericResponse);
    }

    $recent = $pdo->prepare(
        'SELECT COUNT(*) FROM password_reset_tokens WHERE user_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)'
    );
    $recent->execute([(int) $user['id']]);
    if ((int) $recent->fetchColumn() > 0) {
        jsonSuccess($genericResponse);
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')
            ->execute([(int) $user['id']]);
        $pdo->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expired_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
        )->execute([(int) $user['id'], $tokenHash]);
        sendPasswordResetEmail((string) $user['email'], (string) $user['name'], $token);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess($genericResponse);
});
