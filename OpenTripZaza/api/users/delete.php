<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id']);

    $id = (int) $data['id'];
    if ($id <= 0) {
        throw new InvalidArgumentException('Akun tim tidak valid.');
    }

    $statement = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'worker'");
    $statement->execute([$id]);
    if ($statement->rowCount() === 0) {
        jsonError('Akun tim tidak ditemukan.', 404);
    }

    jsonSuccess(['id' => $id]);
});
