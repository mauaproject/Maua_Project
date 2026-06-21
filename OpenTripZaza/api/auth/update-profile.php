<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'email', 'name', 'whatsapp']);
    $email = strtolower(trim((string) $data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $tripProfile = customerTripProfileValues($data);
    $statement = $pdo->prepare(
        "UPDATE users
         SET name=?, whatsapp=?, address=?, age=?, gender=?, health_notes=?,
             blood_type=?, height_cm=?, weight_kg=?, shoe_size=?, updated_at=CURRENT_TIMESTAMP
         WHERE id=? AND email=? AND role='customer'"
    );
    $statement->execute([
        trim((string) $data['name']),
        trim((string) $data['whatsapp']),
        trim((string) ($data['address'] ?? '')) ?: null,
        nullableInt($data['age'] ?? null),
        trim((string) ($data['gender'] ?? '')) ?: null,
        trim((string) ($data['healthNotes'] ?? '')) ?: null,
        ...$tripProfile,
        (int) $data['id'],
        $email,
    ]);
    if ($statement->rowCount() === 0) {
        $exists = $pdo->prepare("SELECT id FROM users WHERE id=? AND email=? AND role='customer'");
        $exists->execute([(int) $data['id'], $email]);
        if (!$exists->fetchColumn()) {
            jsonError('Akun customer tidak ditemukan.', 404);
        }
    }
    $lookup = $pdo->prepare("SELECT * FROM users WHERE id=? AND email=? AND role='customer' LIMIT 1");
    $lookup->execute([(int) $data['id'], $email]);
    jsonSuccess(publicCustomerUser($lookup->fetch()));
});
