<?php
declare(strict_types=1);

require_once __DIR__ . '/mailer.php';

function verificationOtpEmailHtml(string $name, string $otp): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<!doctype html><html><body style="margin:0;background:#f4f1ea;font-family:Arial,sans-serif;color:#26332d">'
        . '<div style="max-width:640px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:#173f35;color:#fff;padding:24px 30px"><strong style="font-size:22px">Maua Project</strong></div>'
        . '<div style="padding:30px"><h1 style="font-size:24px">Verifikasi Email</h1>'
        . '<p>Halo <strong>' . $safeName . '</strong>,</p>'
        . '<p>Terima kasih sudah mendaftar di Maua Project. Masukkan kode berikut pada halaman verifikasi:</p>'
        . '<div style="font-size:34px;font-weight:700;letter-spacing:8px;text-align:center;background:#f4f1ea;padding:18px;border-radius:10px;margin:26px 0">'
        . $safeOtp . '</div>'
        . '<p>Kode ini hanya berlaku selama 30 menit dan hanya dapat digunakan satu kali.</p>'
        . '<p>Jika kamu tidak merasa mendaftar, abaikan email ini.</p>'
        . '<p>Salam,<br><strong>Maua Project</strong></p></div></div></body></html>';
}

function generateVerificationOtp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationOtp(string $email, string $name, string $otp): void
{
    sendSmtpMail(
        $email,
        'Kode Verifikasi Email Maua Project',
        verificationOtpEmailHtml($name, $otp)
    );
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
        'bloodType' => $user['blood_type'] ?? '',
        'heightCm' => $user['height_cm'] ?? '',
        'weightKg' => $user['weight_kg'] ?? '',
        'shoeSize' => $user['shoe_size'] ?? '',
    ];
}
