<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/save-helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id']);
    $pdo->beginTransaction();
    try {
        $id = saveTripRecord($pdo, $data, (int) $data['id']);
        $pdo->commit();
        $statement = $pdo->prepare('SELECT * FROM trips WHERE id = ?');
        $statement->execute([$id]);
        jsonSuccess(mapTrip($pdo, $statement->fetch()));
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
});
