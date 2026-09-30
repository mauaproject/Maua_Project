<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    expireUnpaidReschedules($pdo);
    $slug = trim((string) ($_GET['slug'] ?? ''));
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($slug !== '') {
        $id = array_search($slug, tripSlugMap($pdo), true);
    }
    if (!$id) jsonError('Trip tidak ditemukan.', 404);
    $statement = $pdo->prepare('SELECT * FROM trips WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $trip = $statement->fetch();
    if (!$trip) {
        jsonError('Trip tidak ditemukan.', 404);
    }
    $adminView = ($_GET['all'] ?? '') === '1';
    jsonSuccess(mapTrip($pdo, $trip, !$adminView));
});
