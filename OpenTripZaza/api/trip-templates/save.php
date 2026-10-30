<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/manage-helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    requireTripTemplateManagementSchema($pdo);
    $data = jsonInput();
    $id = isset($data['templateId']) ? tripTemplatePositiveInt($data['templateId'], 'ID template', PHP_INT_MAX) : null;
    $private = !empty($data['isPrivateTrip']);
    validateManagedTripTemplateContent($data, $private);
    $normalized = $private ? null : normalizeManagedTripTemplatePattern($data['scheduleRules'] ?? null);
    $name = trim((string) $data['name']);
    $type = $private ? 'private' : 'open';
    $pdo->beginTransaction();
    try {
        $old = $id ? managedTripTemplateRow($pdo, $id, true) : null;
        if ($old && (int) ($data['expectedSourceTripId'] ?? 0) !== (int) $old['source_trip_id']) {
            throw new InvalidArgumentException('Template sudah diubah. Muat ulang sebelum menyimpan.');
        }
        // Always create a separate source snapshot. A legacy source may itself
        // be a published trip, so it must never be edited or deleted here.
        unset($data['id']);
        $data['name'] = $name . ' - Sumber Template';
        $data['type'] = $type;
        $data['status'] = 'Ditutup';
        $data['schedules'] = [];
        $data['availableStartDate'] = null;
        $data['availableEndDate'] = null;
        $data['addons'] = array_map(static function (array $addon): array {
            unset($addon['id']);
            return $addon;
        }, $data['addons'] ?? []);
        $data['sessions'] = $private ? array_map(static function (array $session): array {
            $session['driveLinkUrl'] = '';
            return $session;
        }, $data['sessions'] ?? []) : [];
        $sourceId = saveTripRecord($pdo, $data);
        $pdo->prepare('UPDATE trips SET is_template_source = 1 WHERE id = ?')->execute([$sourceId]);
        if ($old) {
            $pdo->prepare('UPDATE trip_images target INNER JOIN trip_images original ON original.image_url = target.image_url
                AND original.trip_id = ? SET target.thumbnail_url = original.thumbnail_url WHERE target.trip_id = ?')
                ->execute([(int) $old['source_trip_id'], $sourceId]);
            $pdo->prepare('UPDATE trips target INNER JOIN trips original ON original.id = ?
                SET target.private_notes_en = original.private_notes_en WHERE target.id = ?')
                ->execute([(int) $old['source_trip_id'], $sourceId]);
            $pdo->prepare('UPDATE private_trip_packages target INNER JOIN private_trip_packages original
                ON original.package_code = target.package_code AND original.trip_id = ?
                SET target.name_en = original.name_en, target.destinations_json_en = original.destinations_json_en,
                    target.description_en = original.description_en WHERE target.trip_id = ?')
                ->execute([(int) $old['source_trip_id'], $sourceId]);
        }
        $key = $old['template_key'] ?? $type . '-' . bin2hex(random_bytes(12));
        $pattern = $normalized ? jsonText(['codePrefix' => $key, ...$normalized['pattern']]) : null;
        $label = $normalized['label'] ?? 'Tersedia setiap tanggal dalam bulan terpilih';
        if ($old) {
            $pdo->prepare("UPDATE trip_generation_templates SET source_trip_id = ?, name = ?, trip_type = ?,
                pattern_label = ?, schedule_pattern_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$sourceId, $name, $type, $label, $pattern, $id]);
        } else {
            $pdo->prepare("INSERT INTO trip_generation_templates
                (template_key, source_trip_id, name, trip_type, pattern_label, schedule_pattern_json, status)
                VALUES (?, ?, ?, ?, ?, ?, 'active')")
                ->execute([$key, $sourceId, $name, $type, $label, $pattern]);
            $id = (int) $pdo->lastInsertId();
        }
        $pdo->commit();
        jsonSuccess(managedTripTemplateDetail($pdo, managedTripTemplateRow($pdo, $id)), $old ? 200 : 201);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
});
