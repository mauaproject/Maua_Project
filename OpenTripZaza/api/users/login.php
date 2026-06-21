<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['email', 'password']);
    $statement = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $statement->execute([strtolower(trim((string) $data['email']))]);
    $user = $statement->fetch();
    if (!$user || !$user['password_hash'] || !password_verify((string) $data['password'], $user['password_hash'])) {
        jsonError('Email atau password salah.', 401);
    }
    $requestedRole = $data['role'] ?? null;
    $actualRole = $user['role'] === 'worker' ? 'pekerja' : $user['role'];
    if ($requestedRole && $requestedRole !== $actualRole) {
        jsonError('Akun tidak memiliki akses untuk peran ini.', 403);
    }
    jsonSuccess([
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'emailVerified' => (bool) ($user['email_verified'] ?? false),
        'emailVerifiedAt' => $user['email_verified_at'] ?? null,
        'whatsapp' => $user['whatsapp'] ?? '',
        'role' => $actualRole,
        'address' => $user['address'] ?? '',
        'age' => $user['age'] ?? '',
        'gender' => $user['gender'] ?? '',
        'healthNotes' => $user['health_notes'] ?? '',
        'bloodType' => $user['blood_type'] ?? '',
        'heightCm' => $user['height_cm'] ?? '',
        'weightKg' => $user['weight_kg'] ?? '',
        'shoeSize' => $user['shoe_size'] ?? '',
    ]);
});
