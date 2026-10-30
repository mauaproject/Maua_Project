<?php
declare(strict_types=1);
// CLI only. Requires a disposable MariaDB bound to 127.0.0.1:33079.
// Setup: php api/tests/trip-template-management.php --setup
// Server: DB_HOST=127.0.0.1 DB_PORT=33079 DB_NAME=maua_template_management_test
// DB_USER=maua_template_test DB_PASSWORD=local-template-test APP_TIMEZONE=Asia/Jakarta
// php -S 127.0.0.1:8124 api/tests/trip-template-router.php
// Tests: php api/tests/trip-template-management.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$pdo = new PDO('mysql:host=127.0.0.1;port=33079;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$root = dirname(__DIR__, 2);
if (in_array('--setup', $argv, true)) {
    $pdo->exec('CREATE DATABASE maua_template_management_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE maua_template_management_test');
    // Import schema only, never the real users, bookings, or other dump data.
    $dump = (string) file_get_contents(dirname(__DIR__) . '/migrations/u124793915_maua_project (9).sql');
    preg_match_all('/^CREATE TABLE `[^`]+`[\s\S]*?;/m', $dump, $tables);
    foreach ($tables[0] as $sql) {
        if (preg_match('/^\s*(?:INSERT|UPDATE|DELETE)\s/im', $sql)) throw new RuntimeException('Schema extraction contains data statements.');
        $pdo->exec($sql);
    }
    preg_match_all('/^ALTER TABLE `[^`]+`[\s\S]*?;/m', $dump, $alters);
    foreach ($alters[0] as $sql) {
        if (preg_match('/\bINSERT\s/i', $sql)) throw new RuntimeException('Schema extraction contains data statements.');
        $pdo->exec($sql);
    }
    $generator = (string) file_get_contents(dirname(__DIR__) . '/migrations/2026-08-31-trip-generation-templates.sql');
    preg_match_all('/CREATE TABLE IF NOT EXISTS `[^`]+`[\s\S]*?ENGINE=[^;]+;/', $generator, $tables);
    foreach ($tables[0] as $sql) $pdo->exec($sql);
    foreach (['2026-07-21-booking-reschedules.sql', '2026-09-17-reschedule-fees-and-reservations.sql', '2026-10-14-blog.sql', '2026-10-15-blog-keywords.sql'] as $migration) {
        $pdo->exec((string) file_get_contents(dirname(__DIR__) . '/migrations/' . $migration));
    }
    $migration = (string) file_get_contents(dirname(__DIR__) . '/migrations/2026-10-30-template-management.sql');
    $pdo->exec($migration);
    $pdo->exec($migration);
    $pdo->exec("CREATE USER 'maua_template_test'@'127.0.0.1' IDENTIFIED BY 'local-template-test'");
    $pdo->exec("GRANT ALL PRIVILEGES ON maua_template_management_test.* TO 'maua_template_test'@'127.0.0.1'");
    $insert = $pdo->prepare('INSERT INTO users (id,name,email,password_hash,role,email_verified) VALUES (?,?,?,?,?,1)');
    foreach ([1 => 'admin', 2 => 'worker', 3 => 'customer'] as $id => $role) {
        $insert->execute([$id, 'Template Test ' . $role, 'template-' . $role . '@example.invalid', password_hash('local-template-password', PASSWORD_DEFAULT), $role]);
        $pdo->prepare('INSERT INTO user_sessions (user_id,token_hash,expires_at,last_used_at) VALUES (?,SHA2(?,256),DATE_ADD(NOW(),INTERVAL 2 DAY),NOW())')->execute([$id, 'local-template-' . $role]);
    }
    echo "Isolated schema ready; migration applied twice; no production data imported.\n";
    exit;
}
$pdo->exec('USE maua_template_management_test');
$checks = 0;
function expectTemplate(bool $condition, string $label, string $details = ''): void {
    global $checks;
    if (!$condition) throw new RuntimeException($label . ($details ? ': ' . $details : ''));
    $checks++;
    echo "PASS {$label}\n";
}
function templateRequest(string $path, string $method = 'GET', ?array $data = null, string $token = 'local-template-admin', bool $multipart = false): array {
    $curl = curl_init('http://127.0.0.1:8124' . $path);
    $headers = $token !== '' ? ['Authorization: Bearer ' . $token] : [];
    if ($data !== null && !$multipart) $headers[] = 'Content-Type: application/json';
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 20]);
    if ($data !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $multipart ? $data : json_encode($data));
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return ['status' => $status, 'json' => json_decode($body, true), 'body' => $body];
}
function templatePayload(string $name, bool $private = false): array {
    return [
        'name' => $name, 'isPrivateTrip' => $private, 'price' => 650000,
        'destination' => ['id' => 'Destinasi Uji', 'en' => 'Test Destination'],
        'description' => ['id' => 'Deskripsi Indonesia', 'en' => 'English description'],
        'activities' => ['id' => ['Briefing', 'Eksplorasi'], 'en' => ['Briefing', 'Exploration']],
        'facilities' => ['id' => ['Guide', 'Dokumentasi'], 'en' => ['Guide', 'Documentation']],
        'minParticipants' => 1, 'maxParticipants' => 6, 'maxCustomPax' => 2,
        'pricePerPersonTiers' => [1 => 1300000, 2 => 650000],
        'includeDriveLink' => true, 'h7ReminderSubject' => 'Reminder {nama_trip}', 'h7ReminderBody' => 'Trip pada {tanggal_trip} pukul {jam_trip}',
        'addons' => [['name' => 'Mobil', 'price' => 850000, 'maxParticipantsPerUnit' => 5, 'workerAction' => 'none', 'status' => 'active']],
        'imageUrls' => [], 'privatePackages' => [],
        'sessions' => [['id' => 'PAGI', 'name' => 'Sesi Pagi', 'startTime' => '08:00', 'endTime' => '13:00', 'status' => 'active']],
        'scheduleRules' => [['type' => 'weekly', 'weekdays' => [1], 'sessions' => [['name' => 'Sesi Pagi', 'startTime' => '08:00', 'endTime' => '13:00', 'quota' => 6]]]],
    ];
}
function publishedTemplateSnapshot(PDO $pdo, int $tripId): string {
    $result = [];
    foreach (['trips' => 'id', 'trip_schedules' => 'trip_id', 'trip_sessions' => 'trip_id', 'trip_images' => 'trip_id', 'trip_addons' => 'trip_id', 'private_price_tiers' => 'trip_id', 'private_trip_packages' => 'trip_id', 'bookings' => 'trip_id'] as $table => $column) {
        $statement = $pdo->prepare("SELECT * FROM $table WHERE $column = ? ORDER BY id");
        $statement->execute([$tripId]);
        $result[$table] = $statement->fetchAll();
    }
    $statement = $pdo->prepare('SELECT tiers.* FROM package_price_tiers tiers INNER JOIN private_trip_packages package ON package.id = tiers.package_id WHERE package.trip_id = ? ORDER BY tiers.id');
    $statement->execute([$tripId]);
    $result['packagePriceTiers'] = $statement->fetchAll();
    return json_encode($result);
}
function savedTemplate(array $payload): array {
    $response = templateRequest('/api/trip-templates/save.php', 'POST', $payload);
    if (!in_array($response['status'], [200,201], true)) throw new RuntimeException('Save failed: ' . $response['body']);
    return $response['json']['data'];
}
expectTemplate(templateRequest('/__template_test_health')['json']['database'] === 'maua_template_management_test', 'HTTP uses isolated test database');
foreach (['', 'local-template-worker', 'local-template-customer'] as $token) {
    expectTemplate(templateRequest('/api/trip-templates/manage.php', 'GET', null, $token)['status'] === 403, 'Only admin can list templates');
    expectTemplate(templateRequest('/api/trip-templates/save.php', 'POST', templatePayload('Denied'), $token)['status'] === 403, 'Only admin can save templates');
    expectTemplate(templateRequest('/api/trip-templates/delete.php', 'POST', ['id' => 1], $token)['status'] === 403, 'Only admin can delete templates');
}
expectTemplate(templateRequest('/api/trip-templates/delete.php')['status'] === 405, 'Delete rejects GET');
expectTemplate(templateRequest('/api/trip-templates/save.php')['status'] === 405, 'Save rejects GET');
expectTemplate(templateRequest('/api/trip-templates/preview.php')['status'] === 405, 'Unsaved preview rejects GET');
foreach (['', 'local-template-worker', 'local-template-customer'] as $token) {
    expectTemplate(templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => templatePayload('Preview')['scheduleRules']], $token)['status'] === 403, 'Unsaved preview requires an admin session');
}
$openData = templatePayload('Open Snapshot Test');
$open = savedTemplate($openData);
expectTemplate($open['trip']['name'] === $openData['name'] && $open['type'] === 'open', 'Create template returns reusable form content');
expectTemplate($open['trip']['description']['en'] === 'English description', 'Bilingual content is preserved');
$generated = templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $open['id'], 'year' => 2030, 'month' => 11]);
expectTemplate($generated['status'] === 201, 'Managed template works with existing monthly generator', $generated['body']);
$trip = $generated['json']['data'];
$scheduleId = (int) $pdo->query('SELECT id FROM trip_schedules WHERE trip_id = ' . (int) $trip['id'] . ' LIMIT 1')->fetchColumn();
$pdo->prepare("INSERT INTO bookings (user_id,trip_id,schedule_id,customer_name,customer_email,trip_type,selected_date,participants,price_per_person,total_price,status) VALUES (3,?,?, 'Test Customer','template-customer@example.invalid','open','2030-11-04',1,650000,650000,'Disetujui')")->execute([$trip['id'], $scheduleId]);
$snapshot = publishedTemplateSnapshot($pdo, $trip['id']);
$editedData = [...$openData, 'templateId' => $open['id'], 'expectedSourceTripId' => $open['sourceTripId'], 'name' => 'Edited Open Snapshot', 'price' => 900000, 'addons' => [], 'imageUrls' => []];
$editedData['scheduleRules'][0]['weekdays'] = [6,0];
$edited = savedTemplate($editedData);
expectTemplate($edited['sourceTripId'] !== $open['sourceTripId'], 'Edit creates a new source snapshot');
expectTemplate(publishedTemplateSnapshot($pdo, $trip['id']) === $snapshot, 'Edit preserves published trip, schedules, addons, and booking byte for byte');
expectTemplate(templateRequest('/api/trip-templates/save.php', 'POST', $editedData)['status'] === 422, 'Stale editor cannot overwrite a newer template');
expectTemplate(templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $open['id'], 'year' => 2030, 'month' => 11])['status'] === 422, 'Existing monthly generation remains protected after rename');
expectTemplate(templateRequest('/api/trip-templates/delete.php', 'POST', ['id' => $edited['id'], 'expectedSourceTripId' => $open['sourceTripId']])['status'] === 422, 'Stale deletion cannot remove a changed template');
expectTemplate(templateRequest('/api/trip-templates/delete.php', 'POST', ['id' => $edited['id'], 'expectedSourceTripId' => $edited['sourceTripId']])['status'] === 200, 'A previously used template can be deleted');
expectTemplate(publishedTemplateSnapshot($pdo, $trip['id']) === $snapshot, 'Delete preserves published trip and booking byte for byte');
expectTemplate((int) $pdo->query('SELECT COUNT(*) FROM trip_monthly_generations WHERE trip_id = ' . (int) $trip['id'] . ' AND template_id IS NULL')->fetchColumn() === 1, 'Delete keeps the generation history');
expectTemplate(templateRequest('/api/trip-templates/manage.php?id=' . $edited['id'])['status'] === 404, 'Deleted template disappears from detail');

// Old migrations may point a template directly at a real published trip.
$pdo->prepare("INSERT INTO trip_generation_templates (template_key,source_trip_id,name,trip_type,pattern_label,schedule_pattern_json) VALUES ('legacy-published-source',?,'Legacy Source','open','Setiap Senin',?)")
    ->execute([$trip['id'], json_encode($open['pattern'])]);
$legacyId = (int) $pdo->lastInsertId();
$legacy = savedTemplate([...$openData, 'templateId' => $legacyId, 'expectedSourceTripId' => $trip['id'], 'name' => 'Legacy Safely Edited']);
expectTemplate($legacy['sourceTripId'] !== $trip['id'] && publishedTemplateSnapshot($pdo, $trip['id']) === $snapshot, 'Editing a legacy template never edits its published source');

$privateData = templatePayload('Private Snapshot Test', true);
$private = savedTemplate($privateData);
$response = templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $private['id'], 'year' => 2028, 'month' => 2]);
expectTemplate($response['status'] === 201, 'Private template generates a monthly package', $response['body']);
$privateTrip = $response['json']['data'];
expectTemplate($privateTrip['availableStartDate'] === '2028-02-01' && $privateTrip['availableEndDate'] === '2028-02-29', 'Private Trip covers the full leap-year month');
$privateSnapshot = publishedTemplateSnapshot($pdo, $privateTrip['id']);
$private = savedTemplate([...$privateData, 'templateId' => $private['id'], 'expectedSourceTripId' => $private['sourceTripId'], 'name' => 'Private Changed', 'pricePerPersonTiers' => [1 => 2000000, 2 => 1000000]]);
expectTemplate(publishedTemplateSnapshot($pdo, $privateTrip['id']) === $privateSnapshot, 'Private price edits preserve published price tiers and sessions');
expectTemplate(templateRequest('/api/trip-templates/delete.php', 'POST', ['id' => $private['id'], 'expectedSourceTripId' => $private['sourceTripId']])['status'] === 200 && publishedTemplateSnapshot($pdo, $privateTrip['id']) === $privateSnapshot, 'Private deletion preserves the published package');

$cases = [
    ['Daily', 'daily', [], [], 30],
    ['Every Monday', 'weekly', [1], [], 5],
    ['Weekend', 'weekly', [6,0], [], 9],
    ['Saturday', 'weekly', [6], [], 4],
    ['Second and fourth Sunday', 'monthly', [0], [2,4], 2],
    ['Absent fifth Saturday', 'monthly', [6], [5], 0],
];
foreach ($cases as [$name, $type, $weekdays, $occurrences, $expected]) {
    $payload = templatePayload($name);
    $payload['scheduleRules'][0] = [...$payload['scheduleRules'][0], 'type' => $type, 'weekdays' => $weekdays, 'occurrences' => $occurrences];
    $countsBefore = $pdo->query('SELECT (SELECT COUNT(*) FROM trips) AS trips, (SELECT COUNT(*) FROM trip_generation_templates) AS templates, (SELECT COUNT(*) FROM trip_monthly_generations) AS generations')->fetch();
    $unsaved = templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => $payload['scheduleRules']]);
    expectTemplate($unsaved['status'] === 200 && $unsaved['json']['data']['sessionCount'] === $expected, $name . ' can be previewed before saving a template', $unsaved['body']);
    $countsAfter = $pdo->query('SELECT (SELECT COUNT(*) FROM trips) AS trips, (SELECT COUNT(*) FROM trip_generation_templates) AS templates, (SELECT COUNT(*) FROM trip_monthly_generations) AS generations')->fetch();
    expectTemplate($countsBefore === $countsAfter, $name . ' preview creates no template, trip, or generation');
    if ($name === 'Absent fifth Saturday') {
        expectTemplate($unsaved['json']['data']['rules'][0]['dateCount'] === 0 && str_contains($unsaved['json']['data']['rules'][0]['notice'], 'Sabtu ke-5'), 'An absent explicit fifth occurrence has a per-rule explanation');
    }
    if ($name === 'Saturday') {
        expectTemplate($unsaved['json']['data']['rules'][0]['notice'] === '', 'Weekly rules do not warn about an implicit fifth occurrence');
    }
    $template = savedTemplate($payload);
    $preview = templateRequest('/api/trip-templates/index.php?type=open&year=2026&month=11')['json']['data']['templates'];
    $row = array_values(array_filter($preview, fn(array $row): bool => $row['id'] === $template['id']))[0];
    expectTemplate(count($row['preview']['schedules']) === $expected, $name . ' preview has correct dates');
}
$invalid = templatePayload('Invalid overlap');
$invalid['scheduleRules'][] = $invalid['scheduleRules'][0];
expectTemplate(templateRequest('/api/trip-templates/save.php', 'POST', $invalid)['status'] === 422, 'Duplicate overlapping rules are rejected');
expectTemplate(templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => $invalid['scheduleRules']])['status'] === 422, 'Unsaved preview rejects duplicate rules');
$invalid['scheduleRules'] = [['type' => 'weekly', 'weekdays' => [], 'sessions' => []]];
expectTemplate(templateRequest('/api/trip-templates/save.php', 'POST', $invalid)['status'] === 422, 'Empty day and session choices are rejected');
$invalid = templatePayload('Invalid quota');
$invalid['scheduleRules'][0]['sessions'][0]['quota'] = -1;
expectTemplate(templateRequest('/api/trip-templates/save.php', 'POST', $invalid)['status'] === 422, 'Invalid quota is rejected');
expectTemplate(templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => $invalid['scheduleRules']])['status'] === 422, 'Unsaved preview rejects invalid quota');
$invalid['scheduleRules'][0]['sessions'][0] = ['name' => 'Sesi tidak valid', 'startTime' => '13:00', 'endTime' => '08:00', 'quota' => 5];
expectTemplate(templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => $invalid['scheduleRules']])['status'] === 422, 'Unsaved preview rejects end time before start');
expectTemplate(templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2101, 'month' => 11, 'scheduleRules' => $openData['scheduleRules']])['status'] === 422, 'Unsaved preview validates the example year');
$partial = $openData['scheduleRules'];
$partial[0] = [...$partial[0], 'type' => 'monthly', 'weekdays' => [1,6], 'occurrences' => [5]];
$partialPreview = templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2026, 'month' => 11, 'scheduleRules' => $partial]);
expectTemplate($partialPreview['status'] === 200 && $partialPreview['json']['data']['sessionCount'] === 1 && str_contains($partialPreview['json']['data']['rules'][0]['notice'], 'Sabtu ke-5'), 'Partially absent monthly selections retain valid dates and explain the missing day');
$adminTrips = templateRequest('/api/trips/index.php?all=1');
expectTemplate($adminTrips['status'] === 200, 'Normal admin trip endpoint remains available', $adminTrips['body']);
expectTemplate(count($adminTrips['json']['data']) === 2, 'Dedicated template sources do not appear among real admin packages');
expectTemplate(templateRequest('/api/trips/detail.php?id=' . $legacy['sourceTripId'])['status'] === 404, 'A template source cannot be opened as a normal trip');
expectTemplate(templateRequest('/api/trip-templates/manage.php')['status'] === 200, 'Template listing remains available');

$imageTemplateData = templatePayload('Media Snapshot Test');
$imageTemplate = savedTemplate($imageTemplateData);
$imageFile = tempnam(sys_get_temp_dir(), 'maua-template-image-');
$canvas = imagecreatetruecolor(120, 80);
imagefill($canvas, 0, 0, imagecolorallocate($canvas, 170, 130, 60));
imagepng($canvas, $imageFile);
imagedestroy($canvas);
try {
    $upload = ['template_id' => $imageTemplate['id'], 'expected_source_trip_id' => $imageTemplate['sourceTripId'], 'is_primary' => '1', 'image' => new CURLFile($imageFile, 'image/png', 'test.png')];
    expectTemplate(templateRequest('/api/trip-templates/image.php', 'POST', $upload, '', true)['status'] === 403, 'Template image upload requires an admin session');
    $response = templateRequest('/api/trip-templates/image.php', 'POST', $upload, 'local-template-admin', true);
    expectTemplate($response['status'] === 201, 'Template image upload succeeds', $response['body']);
    $imageUrl = $response['json']['data']['url'];
    $imagePath = $root . parse_url($imageUrl, PHP_URL_PATH);
    $imageTripResponse = templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $imageTemplate['id'], 'year' => 2030, 'month' => 12]);
    expectTemplate($imageTripResponse['status'] === 201, 'Image template generates a published package');
    $imageTrip = $imageTripResponse['json']['data'];
    expectTemplate($imageTrip['imageUrl'] === $imageUrl && is_file($imagePath), 'Published package retains the uploaded template photo');
    $imageSnapshot = publishedTemplateSnapshot($pdo, $imageTrip['id']);
    $imageTemplate = savedTemplate([...$imageTemplateData, 'templateId' => $imageTemplate['id'], 'expectedSourceTripId' => $imageTemplate['sourceTripId'], 'imageUrls' => []]);
    expectTemplate(publishedTemplateSnapshot($pdo, $imageTrip['id']) === $imageSnapshot && is_file($imagePath), 'Removing a template photo preserves published photo records and files');
    expectTemplate(templateRequest('/api/trip-templates/delete.php', 'POST', ['id' => $imageTemplate['id'], 'expectedSourceTripId' => $imageTemplate['sourceTripId']])['status'] === 200
        && publishedTemplateSnapshot($pdo, $imageTrip['id']) === $imageSnapshot && is_file($imagePath), 'Deleting a used image template preserves published photos');
} finally {
    unlink($imageFile);
}

$packageData = templatePayload('Private Package Snapshot', true);
$packageData['privatePackages'] = [['packageCode' => 'STANDARD', 'name' => 'Paket Standar', 'maxCustomPax' => 2, 'pricePerPersonTiers' => [1 => 1300000, 2 => 650000], 'destinations' => ['Goa Uji'], 'description' => 'Paket untuk pengujian', 'status' => 'active']];
$packageTemplate = savedTemplate($packageData);
$response = templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $packageTemplate['id'], 'year' => 2030, 'month' => 12]);
expectTemplate($response['status'] === 201, 'Private package choices and their tiers can be generated', $response['body']);
$packageTrip = $response['json']['data'];
$packageSnapshot = publishedTemplateSnapshot($pdo, $packageTrip['id']);
$packageData['privatePackages'][0]['pricePerPersonTiers'] = [1 => 1800000, 2 => 900000];
$packageTemplate = savedTemplate([...$packageData, 'templateId' => $packageTemplate['id'], 'expectedSourceTripId' => $packageTemplate['sourceTripId']]);
expectTemplate(publishedTemplateSnapshot($pdo, $packageTrip['id']) === $packageSnapshot, 'Editing private package tiers preserves all published package choices and prices');

$exceptionData = templatePayload('Monthly Exception Test');
$exceptionTemplate = savedTemplate($exceptionData);
$statement = $pdo->prepare('SELECT * FROM trip_generation_templates WHERE id = ?');
$statement->execute([$exceptionTemplate['id']]);
$templateSnapshot = $statement->fetch();
$initial = templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2030, 'month' => 11, 'scheduleRules' => $exceptionData['scheduleRules']])['json']['data'];
$exceptionSchedules = array_slice($initial['schedules'], 1);
$exceptionSchedules[] = ['date' => '2030-11-06', 'name' => 'Jadwal Khusus', 'startTime' => '07:00', 'endTime' => '11:00', 'quota' => 4];
$response = templateRequest('/api/trip-templates/generate.php', 'POST', ['templateId' => $exceptionTemplate['id'], 'year' => 2030, 'month' => 11, 'schedules' => $exceptionSchedules]);
expectTemplate($response['status'] === 201, 'Admin can customize monthly dates before publishing a package', $response['body']);
$exceptionTrip = $response['json']['data'];
$dates = array_column($exceptionTrip['schedules'], 'date');
expectTemplate(in_array('2030-11-06', $dates, true) && !in_array('2030-11-04', $dates, true), 'Added and removed dates apply to the new monthly package');
$statement->execute([$exceptionTemplate['id']]);
expectTemplate($statement->fetch() === $templateSnapshot, 'Monthly exceptions never alter template metadata or its saved pattern');
$fresh = templateRequest('/api/trip-templates/preview.php', 'POST', ['year' => 2030, 'month' => 11, 'scheduleRules' => $exceptionData['scheduleRules']])['json']['data'];
expectTemplate($fresh === $initial, 'Template preview remains identical after publishing monthly exceptions');
expectTemplate(publishedTemplateSnapshot($pdo, $trip['id']) === $snapshot && publishedTemplateSnapshot($pdo, $privateTrip['id']) === $privateSnapshot, 'Preview and monthly customization preserve all earlier published trips and bookings');

echo "{$checks} checks passed.\n";
