<?php
declare(strict_types=1);

function requireTripTemplateAdmin(PDO $pdo): array
{
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        jsonError('Akses admin diperlukan.', 403);
    }
    return $user;
}

function tripTemplatePeriod(int $year, int $month): array
{
    if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12) {
        throw new InvalidArgumentException('Bulan atau tahun tidak valid.');
    }
    $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    return [
        'year' => $year,
        'month' => $month,
        'startDate' => $start->format('Y-m-d'),
        'endDate' => $start->modify('last day of this month')->format('Y-m-d'),
        'label' => tripTemplateMonthName($month) . ' ' . $year,
    ];
}

function tripTemplateMonthName(int $month): string
{
    return [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ][$month] ?? '';
}

function tripTemplateMonthNameEnglish(int $month): string
{
    return [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ][$month] ?? '';
}

function tripTemplateShiftMonthText(?string $text, int $month, string $language): ?string
{
    if ($text === null || $text === '') {
        return $text;
    }
    if ($language === 'en') {
        return (string) preg_replace(
            '/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\b/i',
            tripTemplateMonthNameEnglish($month),
            $text
        );
    }
    return (string) preg_replace(
        '/\b(?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\b/u',
        tripTemplateMonthName($month),
        $text
    );
}

function tripTemplateTargetName(string $templateName, int $year, int $month): string
{
    return trim($templateName) . ' - ' . tripTemplateMonthName($month) . ' ' . $year;
}

function tripTemplateRow(PDO $pdo, int $templateId, bool $lock = false): array
{
    $statement = $pdo->prepare(
        'SELECT template.*, source.status source_status
         FROM trip_generation_templates template
         INNER JOIN trips source ON source.id = template.source_trip_id
         WHERE template.id = ? AND template.status = \'active\'' . ($lock ? ' FOR UPDATE' : '')
    );
    $statement->execute([$templateId]);
    $template = $statement->fetch();
    if (!$template) {
        throw new InvalidArgumentException('Template trip tidak ditemukan atau sudah tidak aktif.');
    }
    return $template;
}

function tripTemplateRuleDates(array $rule, DateTimeImmutable $start, DateTimeImmutable $end): array
{
    if (($rule['type'] ?? '') === 'daily') {
        $dates = [];
        for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
            $dates[] = $date;
        }
        return $dates;
    }

    if (($rule['type'] ?? '') !== 'nth_weekday') {
        throw new InvalidArgumentException('Aturan jadwal pada template tidak dikenali.');
    }
    $weekday = (int) ($rule['weekday'] ?? -1);
    $nth = (int) ($rule['nth'] ?? 0);
    if ($weekday < 0 || $weekday > 6 || $nth < 1 || $nth > 5) {
        throw new InvalidArgumentException('Aturan hari pada template tidak valid.');
    }
    $occurrence = 0;
    for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
        if ((int) $date->format('w') !== $weekday) {
            continue;
        }
        $occurrence += 1;
        if ($occurrence === $nth) {
            return [$date];
        }
    }
    return [];
}

function tripTemplateCodePart(string $value, string $fallback): string
{
    $normalized = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '_', trim($value)));
    $normalized = trim($normalized, '_');
    return $normalized !== '' ? $normalized : $fallback;
}

function tripTemplateScheduleCode(string $prefix, string $date, string $sessionCode, int $index): string
{
    $datePart = str_replace('-', '', $date);
    $prefixPart = substr(tripTemplateCodePart($prefix, 'TRIP'), 0, 24);
    $sessionPart = substr(tripTemplateCodePart($sessionCode, 'SESI' . ($index + 1)), 0, 12);
    return substr($prefixPart . '-' . $datePart . '-' . $sessionPart, 0, 50);
}

function buildTripTemplateSchedules(array $template, int $year, int $month): array
{
    if (($template['trip_type'] ?? '') !== 'open') {
        return [];
    }
    $pattern = json_decode((string) ($template['schedule_pattern_json'] ?? ''), true);
    if (!is_array($pattern) || !is_array($pattern['rules'] ?? null)) {
        throw new InvalidArgumentException('Pola jadwal template Open Trip tidak valid.');
    }
    $period = tripTemplatePeriod($year, $month);
    $start = new DateTimeImmutable($period['startDate']);
    $end = new DateTimeImmutable($period['endDate']);
    $prefix = (string) ($pattern['codePrefix'] ?? $template['template_key'] ?? 'TRIP');
    $items = [];
    $keys = [];

    foreach ($pattern['rules'] as $rule) {
        $sessions = is_array($rule['sessions'] ?? null) ? $rule['sessions'] : [];
        foreach (tripTemplateRuleDates((array) $rule, $start, $end) as $date) {
            foreach ($sessions as $session) {
                $startTime = substr((string) ($session['startTime'] ?? ''), 0, 5);
                $endTime = substr((string) ($session['endTime'] ?? ''), 0, 5);
                $dateText = $date->format('Y-m-d');
                $key = $dateText . '|' . $startTime . '|' . $endTime;
                if (isset($keys[$key])) {
                    continue;
                }
                $keys[$key] = true;
                $index = count($items);
                $sessionCode = (string) ($session['code'] ?? 'SESI' . ($index + 1));
                $scheduleCode = tripTemplateScheduleCode($prefix, $dateText, $sessionCode, $index);
                $items[] = [
                    'id' => $scheduleCode,
                    'scheduleCode' => $scheduleCode,
                    'sessionCode' => $sessionCode,
                    'name' => trim((string) ($session['name'] ?? 'Sesi ' . ($index + 1))),
                    'date' => $dateText,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'quota' => (int) ($session['quota'] ?? 0),
                    'bookedCount' => 0,
                    'status' => 'active',
                ];
            }
        }
    }
    usort($items, static fn(array $left, array $right): int => [
        $left['date'], $left['startTime'], $left['endTime'],
    ] <=> [
        $right['date'], $right['startTime'], $right['endTime'],
    ]);
    return $items;
}

function normalizeTripTemplateSchedules(array $template, int $year, int $month, mixed $incoming): array
{
    if ($incoming === null) {
        return buildTripTemplateSchedules($template, $year, $month);
    }
    if (!is_array($incoming) || !$incoming || count($incoming) > 200) {
        throw new InvalidArgumentException('Preview Open Trip harus memiliki 1 sampai 200 jadwal.');
    }
    $pattern = json_decode((string) ($template['schedule_pattern_json'] ?? ''), true) ?: [];
    $prefix = (string) ($pattern['codePrefix'] ?? $template['template_key'] ?? 'TRIP');
    $expectedMonth = sprintf('%04d-%02d', $year, $month);
    $items = [];
    $keys = [];
    $codes = [];

    foreach ($incoming as $index => $schedule) {
        if (!is_array($schedule)) {
            throw new InvalidArgumentException('Data jadwal Open Trip tidak valid.');
        }
        $name = trim((string) ($schedule['name'] ?? $schedule['sessionName'] ?? ''));
        $date = trim((string) ($schedule['date'] ?? ''));
        $startTime = substr(trim((string) ($schedule['startTime'] ?? '')), 0, 5);
        $endTime = substr(trim((string) ($schedule['endTime'] ?? '')), 0, 5);
        $quota = (int) ($schedule['quota'] ?? 0);
        if (
            $name === ''
            || strlen($name) > 100
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || substr($date, 0, 7) !== $expectedMonth
            || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $endTime)
            || $endTime <= $startTime
            || $quota <= 0
        ) {
            throw new InvalidArgumentException('Tanggal, sesi, jam, atau kuota pada preview Open Trip tidak valid.');
        }
        $key = $date . '|' . $startTime . '|' . $endTime;
        if (isset($keys[$key])) {
            throw new InvalidArgumentException('Dua jadwal Open Trip tidak boleh memakai tanggal dan jam yang sama.');
        }
        $keys[$key] = true;
        $sessionCode = (string) ($schedule['sessionCode'] ?? $name);
        $code = tripTemplateScheduleCode($prefix, $date, $sessionCode, (int) $index);
        if (isset($codes[$code])) {
            $code = tripTemplateScheduleCode($prefix, $date, $sessionCode . '_' . ($index + 1), (int) $index);
        }
        $codes[$code] = true;
        $items[] = [
            'id' => $code,
            'scheduleCode' => $code,
            'sessionCode' => $sessionCode,
            'name' => $name,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'quota' => $quota,
            'bookedCount' => 0,
            'status' => 'active',
        ];
    }
    usort($items, static fn(array $left, array $right): int => [
        $left['date'], $left['startTime'], $left['endTime'],
    ] <=> [
        $right['date'], $right['startTime'], $right['endTime'],
    ]);
    return $items;
}

function tripTemplatePrivateSessions(PDO $pdo, int $sourceTripId): array
{
    $statement = $pdo->prepare(
        'SELECT session_code, name, start_time, end_time, status
         FROM trip_sessions WHERE trip_id = ? ORDER BY start_time, id'
    );
    $statement->execute([$sourceTripId]);
    return array_map(static fn(array $session): array => [
        'code' => $session['session_code'] ?? '',
        'name' => $session['name'],
        'startTime' => substr((string) $session['start_time'], 0, 5),
        'endTime' => substr((string) $session['end_time'], 0, 5),
        'status' => $session['status'],
    ], $statement->fetchAll());
}

function tripTemplateCopySummary(PDO $pdo, int $sourceTripId): array
{
    $counts = [];
    foreach ([
        'addons' => 'SELECT COUNT(*) FROM trip_addons WHERE trip_id = ?',
        'images' => 'SELECT COUNT(*) FROM trip_images WHERE trip_id = ?',
        'sessions' => 'SELECT COUNT(*) FROM trip_sessions WHERE trip_id = ?',
        'priceTiers' => 'SELECT COUNT(*) FROM private_price_tiers WHERE trip_id = ?',
        'packages' => 'SELECT COUNT(*) FROM private_trip_packages WHERE trip_id = ?',
    ] as $key => $sql) {
        $statement = $pdo->prepare($sql);
        $statement->execute([$sourceTripId]);
        $counts[$key] = (int) $statement->fetchColumn();
    }
    return $counts;
}

function tripTemplatePayload(PDO $pdo, array $template, int $year, int $month): array
{
    $period = tripTemplatePeriod($year, $month);
    $generationStatement = $pdo->prepare(
        'SELECT generation.trip_id, trip.name
         FROM trip_monthly_generations generation
         INNER JOIN trips trip ON trip.id = generation.trip_id
         WHERE generation.template_id = ? AND generation.period_year = ? AND generation.period_month = ?'
    );
    $generationStatement->execute([(int) $template['id'], $year, $month]);
    $generation = $generationStatement->fetch() ?: null;
    $isPrivate = $template['trip_type'] === 'private';
    return [
        'id' => (int) $template['id'],
        'key' => $template['template_key'],
        'name' => $template['name'],
        'type' => $template['trip_type'],
        'sourceTripId' => (int) $template['source_trip_id'],
        'patternLabel' => $template['pattern_label'],
        'targetName' => tripTemplateTargetName((string) $template['name'], $year, $month),
        'alreadyGenerated' => $generation !== null,
        'generatedTripId' => $generation ? (int) $generation['trip_id'] : null,
        'generatedTripName' => $generation['name'] ?? '',
        'preview' => $isPrivate
            ? [
                'startDate' => $period['startDate'],
                'endDate' => $period['endDate'],
                'sessions' => tripTemplatePrivateSessions($pdo, (int) $template['source_trip_id']),
            ]
            : [
                'schedules' => buildTripTemplateSchedules($template, $year, $month),
            ],
        'copySummary' => tripTemplateCopySummary($pdo, (int) $template['source_trip_id']),
    ];
}

function cloneTripTemplateRecord(PDO $pdo, array $template, int $year, int $month, array $schedules): int
{
    $period = tripTemplatePeriod($year, $month);
    $isPrivate = $template['trip_type'] === 'private';
    $targetName = tripTemplateTargetName((string) $template['name'], $year, $month);
    $statement = $pdo->prepare(
        'INSERT INTO trips
        (name, trip_type, experience_type, status, destination_id, destination_en, description_id, description_en,
         activities_id, activities_en, facilities_id, facilities_en, price, quota, slots, min_participants,
         max_participants, max_custom_pax, available_start_date, available_end_date, private_notes, private_notes_en,
         flexible_schedule, private_booking_mode, include_drive_link, h7_reminder_subject, h7_reminder_body)
         SELECT ?, source.trip_type, source.experience_type, \'Tersedia\', source.destination_id, source.destination_en,
                source.description_id, source.description_en, source.activities_id, source.activities_en,
                source.facilities_id, source.facilities_en, source.price, source.quota, source.slots,
                source.min_participants, source.max_participants, source.max_custom_pax, ?, ?, source.private_notes,
                source.private_notes_en, source.flexible_schedule, source.private_booking_mode,
                source.include_drive_link, source.h7_reminder_subject, source.h7_reminder_body
         FROM trips source WHERE source.id = ? AND source.trip_type = ?'
    );
    $statement->execute([
        $targetName,
        $isPrivate ? $period['startDate'] : null,
        $isPrivate ? $period['endDate'] : null,
        (int) $template['source_trip_id'],
        $template['trip_type'],
    ]);
    if ($statement->rowCount() !== 1) {
        throw new InvalidArgumentException('Trip sumber untuk template tidak ditemukan.');
    }
    $tripId = (int) $pdo->lastInsertId();

    if ($isPrivate) {
        $sourceNotes = $pdo->prepare('SELECT private_notes, private_notes_en FROM trips WHERE id = ?');
        $sourceNotes->execute([(int) $template['source_trip_id']]);
        $notes = $sourceNotes->fetch() ?: [];
        $pdo->prepare('UPDATE trips SET private_notes = ?, private_notes_en = ? WHERE id = ?')->execute([
            tripTemplateShiftMonthText($notes['private_notes'] ?? null, $month, 'id'),
            tripTemplateShiftMonthText($notes['private_notes_en'] ?? null, $month, 'en'),
            $tripId,
        ]);
    }

    $pdo->prepare(
        'INSERT INTO trip_images (trip_id, image_url, thumbnail_url, sort_order)
         SELECT ?, image_url, thumbnail_url, sort_order FROM trip_images WHERE trip_id = ? ORDER BY sort_order, id'
    )->execute([$tripId, (int) $template['source_trip_id']]);
    $pdo->prepare(
        'INSERT INTO trip_addons
         (trip_id, name, price, max_participants_per_unit, worker_action, status, sort_order, created_at, updated_at)
         SELECT ?, name, price, max_participants_per_unit, worker_action, status, sort_order,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
         FROM trip_addons WHERE trip_id = ? ORDER BY sort_order, id'
    )->execute([$tripId, (int) $template['source_trip_id']]);

    if ($isPrivate) {
        clonePrivateTripTemplateChildren($pdo, (int) $template['source_trip_id'], $tripId);
    } else {
        $scheduleInsert = $pdo->prepare(
            'INSERT INTO trip_schedules
             (trip_id, schedule_code, session_name, schedule_date, start_time, end_time, drive_link_url,
              visible_until, archived_at, quota, booked_count, status, created_at)
             VALUES (?,?,?,?,?,?,NULL,DATE_ADD(?, INTERVAL 1 DAY),NULL,?,0,\'active\',CURRENT_TIMESTAMP)'
        );
        $totalQuota = 0;
        foreach ($schedules as $schedule) {
            $scheduleInsert->execute([
                $tripId,
                $schedule['scheduleCode'],
                $schedule['name'],
                $schedule['date'],
                $schedule['startTime'],
                $schedule['endTime'],
                $schedule['date'],
                (int) $schedule['quota'],
            ]);
            $totalQuota += (int) $schedule['quota'];
        }
        $pdo->prepare(
            'UPDATE trips SET quota = ?, slots = ?, min_participants = 1, max_participants = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        )->execute([$totalQuota, $totalQuota, $totalQuota, $tripId]);
    }
    return $tripId;
}

function clonePrivateTripTemplateChildren(PDO $pdo, int $sourceTripId, int $targetTripId): void
{
    $pdo->prepare(
        'INSERT INTO trip_sessions
         (trip_id, session_code, name, start_time, end_time, drive_link_url, status)
         SELECT ?, session_code, name, start_time, end_time, NULL, status
         FROM trip_sessions WHERE trip_id = ? ORDER BY start_time, id'
    )->execute([$targetTripId, $sourceTripId]);
    $pdo->prepare(
        'INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person)
         SELECT ?, pax_count, price_per_person FROM private_price_tiers WHERE trip_id = ? ORDER BY pax_count'
    )->execute([$targetTripId, $sourceTripId]);

    $sourcePackages = $pdo->prepare(
        'SELECT * FROM private_trip_packages WHERE trip_id = ? ORDER BY sort_order, id'
    );
    $sourcePackages->execute([$sourceTripId]);
    $packageInsert = $pdo->prepare(
        'INSERT INTO private_trip_packages
         (trip_id, package_code, name, name_en, price, max_custom_pax, destinations_json, destinations_json_en,
          description, description_en, status, sort_order, created_at, updated_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)'
    );
    $tierSelect = $pdo->prepare(
        'SELECT pax_count, price_per_person FROM package_price_tiers WHERE package_id = ? ORDER BY pax_count'
    );
    $tierInsert = $pdo->prepare(
        'INSERT INTO package_price_tiers
         (package_id, pax_count, price_per_person, created_at, updated_at)
         VALUES (?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)'
    );
    foreach ($sourcePackages->fetchAll() as $package) {
        $packageInsert->execute([
            $targetTripId,
            $package['package_code'],
            $package['name'],
            $package['name_en'],
            $package['price'],
            $package['max_custom_pax'],
            $package['destinations_json'],
            $package['destinations_json_en'],
            $package['description'],
            $package['description_en'],
            $package['status'],
            $package['sort_order'],
        ]);
        $targetPackageId = (int) $pdo->lastInsertId();
        $tierSelect->execute([(int) $package['id']]);
        foreach ($tierSelect->fetchAll() as $tier) {
            $tierInsert->execute([$targetPackageId, $tier['pax_count'], $tier['price_per_person']]);
        }
    }
}
