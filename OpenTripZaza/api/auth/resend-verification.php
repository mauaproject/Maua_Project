<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['email']);
    $email = strtolower(trim((string) $data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }

    $statement = $pdo->prepare(
        'SELECT *, (last_sent_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)) sent_recently
         FROM pending_customer_registrations WHERE email=? LIMIT 1'
    );
    $statement->execute([$email]);
    $pending = $statement->fetch();
    if (!$pending) {
        jsonError('Pendaftaran sementara tidak ditemukan. Silakan daftar kembali.', 404);
    }
    if ((bool) $pending['sent_recently']) {
        jsonError('Tunggu 60 detik sebelum mengirim ulang kode verifikasi.', 429);
    }

    $otp = generateVerificationOtp();
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'UPDATE pending_customer_registrations
             SET otp_hash=?, expired_at=DATE_ADD(NOW(), INTERVAL 30 MINUTE),
                 attempts=0, last_sent_at=NOW()
             WHERE id=?'
        )->execute([password_hash($otp, PASSWORD_DEFAULT), (int) $pending['id']]);
        sendVerificationOtp($email, (string) $pending['name'], $otp);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess(['message' => 'Kode verifikasi berhasil dikirim ulang.']);
});
