<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $rawToken = trim((string) ($_GET['token'] ?? ''));
    if (!preg_match('/^[a-f0-9]{64}$/', $rawToken)) {
        throw new InvalidArgumentException('Token verifikasi tidak valid.');
    }
    $tokenHash = hash('sha256', $rawToken);

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'SELECT evt.id token_id, evt.user_id, evt.expired_at, evt.used_at,
                    (evt.expired_at < NOW()) is_expired, u.*
             FROM email_verification_tokens evt
             INNER JOIN users u ON u.id=evt.user_id
             WHERE evt.token_hash=? LIMIT 1 FOR UPDATE'
        );
        $statement->execute([$tokenHash]);
        $token = $statement->fetch();
        if (!$token) {
            jsonError('Link verifikasi tidak ditemukan.', 404);
        }
        if ($token['used_at'] !== null) {
            jsonError('Link verifikasi sudah pernah digunakan.', 410);
        }
        if ((bool) $token['is_expired']) {
            jsonError('Link verifikasi sudah kedaluwarsa. Silakan kirim ulang email verifikasi.', 410);
        }

        $pdo->prepare(
            'UPDATE users SET email_verified=1, email_verified_at=NOW(), updated_at=CURRENT_TIMESTAMP WHERE id=?'
        )->execute([(int) $token['user_id']]);
        $pdo->prepare(
            'UPDATE email_verification_tokens SET used_at=NOW() WHERE id=?'
        )->execute([(int) $token['token_id']]);
        $pdo->prepare(
            'UPDATE email_verification_tokens SET used_at=COALESCE(used_at, NOW())
             WHERE user_id=? AND id<>? AND used_at IS NULL'
        )->execute([(int) $token['user_id'], (int) $token['token_id']]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    $userStatement = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $userStatement->execute([(int) $token['user_id']]);
    jsonSuccess(publicCustomerUser($userStatement->fetch()));
});
