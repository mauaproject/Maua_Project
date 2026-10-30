<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/manage-helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    requireTripTemplateManagementSchema($pdo);
    $id = tripTemplatePositiveInt($_POST['template_id'] ?? null, 'ID template', PHP_INT_MAX);
    if (!isset($_FILES['image'])) throw new InvalidArgumentException('Pilih gambar template.');
    $stored = null;
    try {
        $pdo->beginTransaction();
        $template = managedTripTemplateRow($pdo, $id, true);
        if ((int) ($_POST['expected_source_trip_id'] ?? 0) !== (int) $template['source_trip_id']) {
            throw new InvalidArgumentException('Template sudah diubah. Muat ulang sebelum mengunggah gambar.');
        }
        $sourceId = (int) $template['source_trip_id'];
        $owned = $pdo->prepare('SELECT is_template_source FROM trips WHERE id = ?');
        $owned->execute([$sourceId]);
        if (!(bool) $owned->fetchColumn()) {
            throw new InvalidArgumentException('Simpan template terlebih dahulu sebelum menambahkan gambar.');
        }
        $stored = storeUploadedImage($_FILES['image'], 'trips', 10);
        $primary = boolValue($_POST['is_primary'] ?? false);
        if ($primary) {
            $pdo->prepare('UPDATE trip_images SET sort_order = sort_order + 1 WHERE trip_id = ?')->execute([$sourceId]);
            $sort = 0;
        } else {
            $statement = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM trip_images WHERE trip_id = ?');
            $statement->execute([$sourceId]);
            $sort = (int) $statement->fetchColumn();
        }
        $pdo->prepare('INSERT INTO trip_images (trip_id, image_url, thumbnail_url, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$sourceId, $stored['path'], $stored['thumbnailPath'] ?? null, $sort]);
        $pdo->commit();
        jsonSuccess(['url' => $stored['path']], 201);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($stored) deleteStoredUpload($stored['path'], 'trips');
        if (!empty($stored['thumbnailPath'])) deleteStoredUpload($stored['thumbnailPath'], 'trips');
        throw $error;
    }
});
