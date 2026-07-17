<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'status', 'adminEmail']);
    $adminEmail = strtolower(trim((string) $data['adminEmail']));
    $adminStatement = $pdo->prepare("SELECT id FROM users WHERE email=? AND role='admin' LIMIT 1");
    $adminStatement->execute([$adminEmail]);
    if (!$adminStatement->fetch()) {
        jsonError('Akses admin diperlukan.', 403);
    }
    $status = (string) $data['status'];
    if (!in_array($status, ['approved', 'hidden', 'deleted'], true)) {
        throw new InvalidArgumentException('Status review tidak valid.');
    }
    $deletedAt = $status === 'deleted' ? date('Y-m-d H:i:s') : null;
    $statement = $pdo->prepare(
        "UPDATE reviews
         SET status=?, deleted_at=?, updated_at=CURRENT_TIMESTAMP
         WHERE id=?"
    );
    $statement->execute([$status, $deletedAt, (int) $data['id']]);
    if ($statement->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT id FROM reviews WHERE id=?');
        $exists->execute([(int) $data['id']]);
        if (!$exists->fetch()) {
            jsonError('Review tidak ditemukan.', 404);
        }
    }
    jsonSuccess(['id' => (int) $data['id'], 'status' => $status]);
});
