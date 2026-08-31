<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    if (!tableHasColumn($pdo, 'trip_generation_templates', 'template_key')) {
        throw new InvalidArgumentException(
            'Jalankan migration 2026-08-31-trip-generation-templates.sql sebelum memakai generator paket.'
        );
    }
    $data = jsonInput();
    $templateId = (int) ($data['templateId'] ?? 0);
    $year = (int) ($data['year'] ?? 0);
    $month = (int) ($data['month'] ?? 0);
    if ($templateId <= 0) {
        throw new InvalidArgumentException('Pilih template trip terlebih dahulu.');
    }
    tripTemplatePeriod($year, $month);

    $pdo->beginTransaction();
    try {
        $template = tripTemplateRow($pdo, $templateId, true);
        $existing = $pdo->prepare(
            'SELECT generation.trip_id, trip.name
             FROM trip_monthly_generations generation
             INNER JOIN trips trip ON trip.id = generation.trip_id
             WHERE generation.template_id = ? AND generation.period_year = ? AND generation.period_month = ?
             FOR UPDATE'
        );
        $existing->execute([$templateId, $year, $month]);
        $existingTrip = $existing->fetch();
        if ($existingTrip) {
            throw new InvalidArgumentException(
                'Paket ' . $existingTrip['name'] . ' sudah tersedia untuk periode tersebut.'
            );
        }
        $targetName = tripTemplateTargetName((string) $template['name'], $year, $month);
        $sameName = $pdo->prepare(
            'SELECT id FROM trips WHERE trip_type = ? AND name = ? LIMIT 1 FOR UPDATE'
        );
        $sameName->execute([$template['trip_type'], $targetName]);
        if ($sameName->fetch()) {
            throw new InvalidArgumentException(
                "Paket {$targetName} sudah ada. Gunakan nama/periode lain atau edit paket tersebut."
            );
        }
        $schedules = $template['trip_type'] === 'open'
            ? normalizeTripTemplateSchedules($template, $year, $month, $data['schedules'] ?? null)
            : [];
        $tripId = cloneTripTemplateRecord($pdo, $template, $year, $month, $schedules);
        $generation = $pdo->prepare(
            'INSERT INTO trip_monthly_generations (template_id, trip_id, period_year, period_month)
             VALUES (?,?,?,?)'
        );
        $generation->execute([$templateId, $tripId, $year, $month]);
        $pdo->commit();

        $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE id = ?');
        $tripStatement->execute([$tripId]);
        jsonSuccess(mapTrip($pdo, $tripStatement->fetch()), 201);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
