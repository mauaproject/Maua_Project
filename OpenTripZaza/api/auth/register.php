<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['name', 'email', 'password']);
    $email = strtolower(trim((string) $data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    if (strlen((string) $data['password']) < 6) {
        throw new InvalidArgumentException('Password minimal 6 karakter.');
    }

    $existing = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $existing->execute([$email]);
    if ($existing->fetchColumn()) {
        jsonError('Email sudah terdaftar. Silakan login.', 409);
    }

    $recentPending = $pdo->prepare(
        'SELECT COUNT(*) FROM pending_customer_registrations
         WHERE email=? AND last_sent_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)'
    );
    $recentPending->execute([$email]);
    if ((int) $recentPending->fetchColumn() > 0) {
        jsonError('Kode baru saja dikirim. Tunggu 60 detik atau buka halaman verifikasi.', 429);
    }

    $otp = generateVerificationOtp();
    $tripProfile = customerTripProfileValues($data);
    $values = [
        trim((string) $data['name']),
        $email,
        password_hash((string) $data['password'], PASSWORD_DEFAULT),
        $data['whatsapp'] ?? null,
        $data['address'] ?? null,
        nullableInt($data['age'] ?? null),
        $data['gender'] ?? null,
        $data['healthNotes'] ?? null,
        ...$tripProfile,
        password_hash($otp, PASSWORD_DEFAULT),
    ];

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'INSERT INTO pending_customer_registrations
             (name, email, password_hash, whatsapp, address, age, gender, health_notes,
              blood_type, height_cm, weight_kg, shoe_size, otp_hash, expired_at, attempts, last_sent_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE),0,NOW())
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), password_hash=VALUES(password_hash), whatsapp=VALUES(whatsapp),
                address=VALUES(address), age=VALUES(age), gender=VALUES(gender),
                health_notes=VALUES(health_notes), blood_type=VALUES(blood_type),
                height_cm=VALUES(height_cm), weight_kg=VALUES(weight_kg),
                shoe_size=VALUES(shoe_size), otp_hash=VALUES(otp_hash),
                expired_at=DATE_ADD(NOW(), INTERVAL 30 MINUTE), attempts=0,
                last_sent_at=NOW(), updated_at=CURRENT_TIMESTAMP'
        );
        $statement->execute($values);
        sendVerificationOtp($email, (string) $data['name'], $otp);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess([
        'email' => $email,
        'expiresInMinutes' => 30,
        'message' => 'Kode verifikasi telah dikirim ke email.',
    ], 201);
});
