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

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "INSERT INTO users
             (name, email, email_verified, password_hash, whatsapp, role, address, age, gender, health_notes)
             VALUES (?,?,0,?,?, 'customer',?,?,?,?)"
        );
        $statement->execute([
            trim((string) $data['name']),
            $email,
            password_hash((string) $data['password'], PASSWORD_DEFAULT),
            $data['whatsapp'] ?? null,
            $data['address'] ?? null,
            nullableInt($data['age'] ?? null),
            $data['gender'] ?? null,
            $data['healthNotes'] ?? null,
        ]);
        $userId = (int) $pdo->lastInsertId();
        $userStatement = $pdo->prepare('SELECT * FROM users WHERE id=?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();
        createAndSendVerification($pdo, $user);
        $pdo->commit();
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((string) $exception->getCode() === '23000') {
            jsonError('Email sudah terdaftar.', 409);
        }
        throw $exception;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    jsonSuccess(publicCustomerUser($user), 201);
});
