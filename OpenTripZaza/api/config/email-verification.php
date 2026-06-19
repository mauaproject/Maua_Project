<?php
declare(strict_types=1);

require_once __DIR__ . '/mailer.php';

function verificationBaseUrl(): string
{
    $configured = rtrim(trim((string) getenv('APP_BASE_URL')), '/');
    if ($configured !== '') {
        return $configured;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        throw new RuntimeException('APP_BASE_URL wajib diisi untuk membuat link verifikasi email.');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}

function verificationEmailHtml(string $name, string $verificationUrl): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<!doctype html><html><body style="margin:0;background:#f4f1ea;font-family:Arial,sans-serif;color:#26332d">'
        . '<div style="max-width:640px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:#173f35;color:#fff;padding:24px 30px"><strong style="font-size:22px">Maua Project</strong></div>'
        . '<div style="padding:30px"><h1 style="font-size:24px">Verifikasi Email</h1>'
        . '<p>Halo <strong>' . $safeName . '</strong>,</p>'
        . '<p>Terima kasih sudah mendaftar di Maua Project. Silakan verifikasi email kamu dengan menekan tombol berikut:</p>'
        . '<p style="margin:28px 0"><a href="' . $safeUrl . '" style="display:inline-block;background:#173f35;color:#fff;padding:13px 20px;border-radius:8px;text-decoration:none">Verifikasi Email</a></p>'
        . '<p>Atau buka link berikut:<br><a href="' . $safeUrl . '">' . $safeUrl . '</a></p>'
        . '<p>Link ini hanya berlaku selama 30 menit dan hanya dapat digunakan satu kali.</p>'
        . '<p>Jika kamu tidak merasa mendaftar, abaikan email ini.</p>'
        . '<p>Salam,<br><strong>Maua Project</strong></p></div></div></body></html>';
}

function createAndSendVerification(PDO $pdo, array $user): void
{
    $userId = (int) $user['id'];
    $email = strtolower(trim((string) $user['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email user tidak valid.');
    }

    $pdo->prepare(
        'UPDATE email_verification_tokens
         SET used_at=COALESCE(used_at, NOW())
         WHERE user_id=? AND used_at IS NULL'
    )->execute([$userId]);

    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $insert = $pdo->prepare(
        'INSERT INTO email_verification_tokens (user_id, token_hash, expired_at)
         VALUES (?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
    );
    $insert->execute([$userId, $tokenHash]);

    $verificationUrl = verificationBaseUrl() . '/verify-email?token=' . rawurlencode($rawToken);
    try {
        sendSmtpMail(
            $email,
            'Verifikasi Email Maua Project',
            verificationEmailHtml((string) $user['name'], $verificationUrl)
        );
    } catch (Throwable $exception) {
        $pdo->prepare('DELETE FROM email_verification_tokens WHERE token_hash=?')->execute([$tokenHash]);
        throw $exception;
    }
}

function publicCustomerUser(array $user): array
{
    return [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'emailVerified' => (bool) $user['email_verified'],
        'emailVerifiedAt' => $user['email_verified_at'] ?? null,
        'whatsapp' => $user['whatsapp'] ?? '',
        'role' => $user['role'] === 'worker' ? 'pekerja' : $user['role'],
        'address' => $user['address'] ?? '',
        'age' => $user['age'] ?? '',
        'gender' => $user['gender'] ?? '',
        'healthNotes' => $user['health_notes'] ?? '',
    ];
}
