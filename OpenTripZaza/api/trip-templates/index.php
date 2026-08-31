<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    if (!tableHasColumn($pdo, 'trip_generation_templates', 'template_key')) {
        throw new InvalidArgumentException(
            'Jalankan migration 2026-08-31-trip-generation-templates.sql sebelum memakai generator paket.'
        );
    }
    $nextMonth = (new DateTimeImmutable('first day of next month'));
    $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) $nextMonth->format('Y');
    $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) $nextMonth->format('n');
    $period = tripTemplatePeriod($year, $month);
    $type = ($_GET['type'] ?? '') === 'private' ? 'private' : 'open';
    $statement = $pdo->prepare(
        'SELECT template.*
         FROM trip_generation_templates template
         INNER JOIN trips source ON source.id = template.source_trip_id
         WHERE template.status = \'active\' AND template.trip_type = ?
         ORDER BY template.sort_order, template.name, template.id'
    );
    $statement->execute([$type]);
    $templates = array_map(
        fn(array $template): array => tripTemplatePayload($pdo, $template, $year, $month),
        $statement->fetchAll()
    );
    jsonSuccess([
        'period' => $period,
        'type' => $type,
        'templates' => $templates,
    ]);
});
