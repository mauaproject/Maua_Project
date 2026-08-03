<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['token', 'password']);
    $token = trim((string) $data['token']);
    $password = (string) $data['password'];
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        jsonError('Tautan reset tidak valid atau sudah kedaluwarsa.', 422);
    }
    if (strlen($password) < 8) {
        throw new InvalidArgumentException('Password baru minimal 8 karakter.');
    }

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "SELECT prt.id, prt.user_id
             FROM password_reset_tokens prt
             INNER JOIN users u ON u.id=prt.user_id
             WHERE prt.token_hash=? AND prt.used_at IS NULL AND prt.expired_at>NOW() AND u.role='customer'
             LIMIT 1 FOR UPDATE"
        );
        $statement->execute([hash('sha256', $token)]);
        $reset = $statement->fetch();
        if (!$reset) {
            $pdo->rollBack();
            jsonError('Tautan reset tidak valid atau sudah kedaluwarsa.', 422);
        }
        $userId = (int) $reset['user_id'];
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
        $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')
            ->execute([$userId]);
        $pdo->prepare('DELETE FROM user_sessions WHERE user_id=?')->execute([$userId]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess(['message' => 'Password berhasil diperbarui. Silakan login kembali.']);
});
