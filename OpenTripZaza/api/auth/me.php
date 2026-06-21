<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/email-verification.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $email = strtolower(trim((string) ($_GET['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $statement = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='customer' LIMIT 1");
    $statement->execute([$email]);
    $user = $statement->fetch();
    if (!$user) {
        jsonError('Akun customer tidak ditemukan.', 404);
    }
    jsonSuccess(publicCustomerUser($user));
});
