<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new InvalidArgumentException('ID trip tidak valid.');
    }
    $statement = $pdo->prepare('SELECT * FROM trips WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $trip = $statement->fetch();
    if (!$trip) {
        jsonError('Trip tidak ditemukan.', 404);
    }
    jsonSuccess(mapTrip($pdo, $trip));
});
