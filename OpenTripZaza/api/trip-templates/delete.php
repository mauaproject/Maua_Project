<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/manage-helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    requireTripTemplateManagementSchema($pdo);
    $data = jsonInput();
    $id = tripTemplatePositiveInt($data['id'] ?? null, 'ID template', PHP_INT_MAX);
    $pdo->beginTransaction();
    try {
        $template = managedTripTemplateRow($pdo, $id, true);
        if ((int) ($data['expectedSourceTripId'] ?? 0) !== (int) $template['source_trip_id']) {
            throw new InvalidArgumentException('Template sudah diubah. Muat ulang sebelum menghapus.');
        }
        // The SET NULL foreign key preserves generation history. No source,
        // published trip, booking, schedule, or media file is removed.
        $pdo->prepare('DELETE FROM trip_generation_templates WHERE id = ?')->execute([$id]);
        $pdo->commit();
        jsonSuccess(['id' => $id]);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
});
