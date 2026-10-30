<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/manage-helper.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    requireTripTemplateAdmin($pdo);
    requireTripTemplateManagementSchema($pdo);
    if (isset($_GET['id'])) {
        $id = tripTemplatePositiveInt($_GET['id'], 'ID template', PHP_INT_MAX);
        jsonSuccess(managedTripTemplateDetail($pdo, managedTripTemplateRow($pdo, $id)));
    }
    $rows = $pdo->query('SELECT * FROM trip_generation_templates ORDER BY sort_order, name, id')->fetchAll();
    jsonSuccess(['templates' => array_map('managedTripTemplatePayload', $rows)]);
});
