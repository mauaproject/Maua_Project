<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id']);
    $statement = $pdo->prepare("UPDATE trips SET status = 'Ditutup', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $statement->execute([(int) $data['id']]);
    if ($statement->rowCount() === 0) {
        jsonError('Trip tidak ditemukan.', 404);
    }
    jsonSuccess(['id' => (int) $data['id'], 'status' => 'Ditutup']);
});
