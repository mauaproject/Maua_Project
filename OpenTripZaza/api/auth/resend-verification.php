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

    $statement = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='customer' LIMIT 1");
    $statement->execute([$email]);
    $user = $statement->fetch();
    if (!$user) {
        jsonError('Akun customer tidak ditemukan.', 404);
    }
    if ((bool) $user['email_verified']) {
        jsonSuccess(['emailVerified' => true, 'message' => 'Email sudah terverifikasi.']);
    }

    $recent = $pdo->prepare(
        'SELECT COUNT(*) FROM email_verification_tokens
         WHERE user_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)'
    );
    $recent->execute([(int) $user['id']]);
    if ((int) $recent->fetchColumn() > 0) {
        jsonError('Tunggu 60 detik sebelum mengirim ulang email verifikasi.', 429);
    }

    createAndSendVerification($pdo, $user);
    jsonSuccess(['emailVerified' => false, 'message' => 'Email verifikasi berhasil dikirim ulang.']);
});
