<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $email = trim((string) ($_GET['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email tidak valid.');
    }
    $statement = $pdo->prepare('SELECT * FROM bookings WHERE customer_email = ? ORDER BY id DESC');
    $statement->execute([$email]);
    jsonSuccess(array_map(fn(array $row): array => mapBooking($pdo, $row), $statement->fetchAll()));
});
