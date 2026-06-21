<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
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
    $role = ($data['role'] ?? 'customer') === 'pekerja' ? 'worker' : ($data['role'] ?? 'customer');
    if (!in_array($role, ['customer', 'worker'], true)) {
        throw new InvalidArgumentException('Role user tidak valid.');
    }
    $tripProfile = customerTripProfileValues($data);
    try {
        $statement = $pdo->prepare(
            'INSERT INTO users (name, email, email_verified, email_verified_at, password_hash, whatsapp, role, address, age, gender, health_notes, blood_type, height_cm, weight_kg, shoe_size)
             VALUES (?,?,?,IF(?=1,NOW(),NULL),?,?,?,?,?,?,?,?,?,?,?)'
        );
        $isVerified = $role === 'worker' ? 1 : 0;
        $statement->execute([
            trim((string) $data['name']),
            $email,
            $isVerified,
            $isVerified,
            password_hash((string) $data['password'], PASSWORD_DEFAULT),
            $data['whatsapp'] ?? null,
            $role,
            $data['address'] ?? null,
            nullableInt($data['age'] ?? null),
            $data['gender'] ?? null,
            $data['healthNotes'] ?? null,
            ...$tripProfile,
        ]);
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            jsonError('Email sudah terdaftar.', 409);
        }
        throw $exception;
    }
    jsonSuccess(['id' => (int) $pdo->lastInsertId(), 'name' => $data['name'], 'email' => $email, 'role' => $role === 'worker' ? 'pekerja' : $role], 201);
});
