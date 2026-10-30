<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/manage-helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    $data = jsonInput();
    $year = tripTemplatePositiveInt($data['year'] ?? null, 'Tahun contoh', 2100);
    $month = tripTemplatePositiveInt($data['month'] ?? null, 'Bulan contoh', 12);
    jsonSuccess(previewManagedTripTemplatePattern($data['scheduleRules'] ?? null, $year, $month));
});
