<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['email', 'otp']);
    $email = strtolower(trim((string) $data['email']));
    $otp = trim((string) $data['otp']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otp)) {
        throw new InvalidArgumentException('Email atau kode OTP tidak valid.');
    }

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'SELECT *, (expired_at < NOW()) is_expired
             FROM pending_customer_registrations WHERE email=? LIMIT 1 FOR UPDATE'
        );
        $statement->execute([$email]);
        $pending = $statement->fetch();
        if (!$pending) {
            $pdo->rollBack();
            jsonError('Pendaftaran sementara tidak ditemukan. Silakan daftar kembali.', 404);
        }
        if ((bool) $pending['is_expired']) {
            $pdo->rollBack();
            jsonError('Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode.', 410);
        }
        if ((int) $pending['attempts'] >= 5) {
            $pdo->rollBack();
            jsonError('Terlalu banyak percobaan. Silakan kirim ulang kode verifikasi.', 429);
        }
        if (!password_verify($otp, (string) $pending['otp_hash'])) {
            $pdo->prepare(
                'UPDATE pending_customer_registrations SET attempts=attempts+1 WHERE id=?'
            )->execute([(int) $pending['id']]);
            $pdo->commit();
            jsonError('Kode OTP salah.', 422);
        }

        $insert = $pdo->prepare(
            "INSERT INTO users
             (name, email, email_verified, email_verified_at, password_hash, whatsapp,
              role, address, age, gender, health_notes, blood_type, height_cm, weight_kg, shoe_size)
             VALUES (?,?,1,NOW(),?,?,'customer',?,?,?,?,?,?,?,?)"
        );
        $insert->execute([
            $pending['name'],
            $pending['email'],
            $pending['password_hash'],
            $pending['whatsapp'],
            $pending['address'],
            $pending['age'],
            $pending['gender'],
            $pending['health_notes'],
            $pending['blood_type'],
            $pending['height_cm'],
            $pending['weight_kg'],
            $pending['shoe_size'],
        ]);
        $userId = (int) $pdo->lastInsertId();
        $pdo->prepare('DELETE FROM pending_customer_registrations WHERE id=?')
            ->execute([(int) $pending['id']]);
        $pdo->commit();
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((string) $exception->getCode() === '23000') {
            jsonError('Email sudah berhasil terdaftar. Silakan login.', 409);
        }
        throw $exception;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess([
        'id' => $userId,
        'email' => $email,
        'emailVerified' => true,
        'message' => 'Akun berhasil dibuat. Silakan login.',
    ], 201);
});
