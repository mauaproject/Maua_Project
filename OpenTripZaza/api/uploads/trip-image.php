<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $tripId = filter_input(INPUT_POST, 'trip_id', FILTER_VALIDATE_INT);
    if (!$tripId || !isset($_FILES['image'])) {
        throw new InvalidArgumentException('trip_id dan file image wajib diisi.');
    }
    $exists = $pdo->prepare('SELECT id FROM trips WHERE id = ?');
    $exists->execute([$tripId]);
    if (!$exists->fetch()) {
        jsonError('Trip tidak ditemukan.', 404);
    }
    $stored = storeUploadedImage($_FILES['image'], 'trips', 10);
    $isPrimary = filter_var($_POST['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN);
    try {
        $pdo->beginTransaction();
        if ($isPrimary) {
            $pdo->prepare('UPDATE trip_images SET sort_order = sort_order + 1 WHERE trip_id = ?')->execute([$tripId]);
            $sortOrder = 0;
        } else {
            $sortStatement = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM trip_images WHERE trip_id = ?');
            $sortStatement->execute([$tripId]);
            $sortOrder = (int) $sortStatement->fetchColumn();
        }
        $statement = $pdo->prepare('INSERT INTO trip_images (trip_id, image_url, sort_order) VALUES (?,?,?)');
        $statement->execute([$tripId, $stored['path'], $sortOrder]);
        $imageId = (int) $pdo->lastInsertId();
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        deleteStoredUpload($stored['path'], 'trips');
        throw $error;
    }
    jsonSuccess(['id' => $imageId, 'tripId' => $tripId, 'url' => $stored['path'], 'isPrimary' => $isPrimary], 201);
});
