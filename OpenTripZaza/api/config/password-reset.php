<?php
declare(strict_types=1);

require_once __DIR__ . '/mailer.php';

function passwordResetEmailHtml(string $name, string $resetUrl): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<!doctype html><html><body style="margin:0;background:#f4f1ea;font-family:Arial,sans-serif;color:#26332d">'
        . '<div style="max-width:640px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:#173f35;color:#fff;padding:24px 30px"><strong style="font-size:22px">Maua Project</strong></div>'
        . '<div style="padding:30px"><h1 style="font-size:24px">Atur Ulang Password</h1>'
        . '<p>Halo <strong>' . $safeName . '</strong>,</p>'
        . '<p>Kami menerima permintaan untuk mengatur ulang password akunmu.</p>'
        . '<p style="margin:28px 0"><a href="' . $safeUrl . '" style="display:inline-block;background:#173f35;color:#fff;text-decoration:none;padding:14px 22px;border-radius:9px;font-weight:700">Buat Password Baru</a></p>'
        . '<p>Tautan ini hanya berlaku selama 30 menit dan hanya dapat digunakan satu kali.</p>'
        . '<p>Jika kamu tidak meminta reset password, abaikan email ini. Password akunmu tetap aman.</p>'
        . '<p>Salam,<br><strong>Maua Project</strong></p></div></div></body></html>';
}

function sendPasswordResetEmail(string $email, string $name, string $token): void
{
    $baseUrl = rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/');
    if ($baseUrl === '') {
        throw new RuntimeException('Konfigurasi APP_BASE_URL belum diisi.');
    }
    $resetUrl = $baseUrl . '/reset-password?token=' . rawurlencode($token);
    sendSmtpMail($email, 'Atur Ulang Password Maua Project', passwordResetEmailHtml($name, $resetUrl));
}
