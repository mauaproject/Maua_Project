<?php
declare(strict_types=1);

require_once __DIR__ . '/helper.php';
require_once dirname(__DIR__) . '/trips/save-helper.php';

function requireTripTemplateManagementSchema(PDO $pdo): void
{
    if (!tableHasColumn($pdo, 'trip_generation_templates', 'template_key')
        || !tableHasColumn($pdo, 'trips', 'is_template_source')) {
        throw new InvalidArgumentException('Pengelolaan template memerlukan pembaruan database. Hubungi pengelola website.');
    }
    $constraint = $pdo->query("SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'trip_monthly_generations'
          AND CONSTRAINT_NAME = 'fk_trip_monthly_generation_template'")->fetchColumn();
    if ($constraint !== 'SET NULL') {
        throw new InvalidArgumentException('Pengelolaan template memerlukan pembaruan database agar riwayat paket tetap tersimpan. Hubungi pengelola website.');
    }
}

function managedTripTemplateRow(PDO $pdo, int $id, bool $lock = false): array
{
    $statement = $pdo->prepare('SELECT * FROM trip_generation_templates WHERE id = ?' . ($lock ? ' FOR UPDATE' : ''));
    $statement->execute([$id]);
    $template = $statement->fetch();
    if (!$template) {
        jsonError('Template tidak ditemukan atau sudah dihapus.', 404);
    }
    return $template;
}

function managedTripTemplatePayload(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'type' => $row['trip_type'],
        'patternLabel' => $row['pattern_label'],
        'sourceTripId' => (int) $row['source_trip_id'],
    ];
}

function managedTripTemplateDetail(PDO $pdo, array $row): array
{
    $statement = $pdo->prepare('SELECT * FROM trips WHERE id = ?');
    $statement->execute([(int) $row['source_trip_id']]);
    $source = $statement->fetch();
    if (!$source) {
        throw new InvalidArgumentException('Isi template tidak ditemukan.');
    }
    $trip = mapTrip($pdo, $source);
    unset($trip['id']);
    $trip['name'] = $row['name'];
    return [
        ...managedTripTemplatePayload($row),
        'pattern' => json_decode((string) ($row['schedule_pattern_json'] ?? ''), true),
        'trip' => $trip,
    ];
}

function tripTemplatePositiveInt(mixed $value, string $label, int $maximum = 100000): int
{
    $number = filter_var($value, FILTER_VALIDATE_INT);
    if ($number === false || $number < 1 || $number > $maximum) {
        throw new InvalidArgumentException($label . ' harus berupa bilangan bulat positif.');
    }
    return $number;
}

function normalizeManagedTripTemplatePattern(mixed $input): array
{
    if (!is_array($input) || !$input || count($input) > 30) {
        throw new InvalidArgumentException('Isi 1 sampai 30 aturan jadwal Open Trip.');
    }
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $rules = [];
    $labels = [];
    $groups = [];
    $occupied = [];
    foreach ($input as $ruleIndex => $rule) {
        $firstCompiledRule = count($rules);
        if (!is_array($rule) || !in_array($rule['type'] ?? '', ['daily', 'weekly', 'monthly'], true)) {
            throw new InvalidArgumentException('Pilih pola harian, mingguan, atau hari tertentu dalam bulan.');
        }
        $type = $rule['type'];
        $weekdays = $type === 'daily' ? range(0, 6) : ($rule['weekdays'] ?? []);
        $occurrences = $type === 'monthly' ? ($rule['occurrences'] ?? []) : range(1, 5);
        if (!is_array($weekdays) || !$weekdays || !is_array($occurrences) || !$occurrences) {
            throw new InvalidArgumentException('Pilih hari dan urutan kemunculan untuk setiap aturan.');
        }
        foreach ($weekdays as $day) {
            if (filter_var($day, FILTER_VALIDATE_INT) === false || (int) $day < 0 || (int) $day > 6) {
                throw new InvalidArgumentException('Pilihan hari tidak valid.');
            }
        }
        foreach ($occurrences as $occurrence) {
            tripTemplatePositiveInt($occurrence, 'Urutan hari', 5);
        }
        $weekdays = array_values(array_unique(array_map('intval', $weekdays)));
        $occurrences = array_values(array_unique(array_map('intval', $occurrences)));
        usort($weekdays, static fn(int $left, int $right): int => ($left === 0 ? 7 : $left) <=> ($right === 0 ? 7 : $right));
        sort($occurrences);
        $sessions = $rule['sessions'] ?? [];
        if (!is_array($sessions) || !$sessions || count($sessions) > 20) {
            throw new InvalidArgumentException('Setiap aturan memerlukan 1 sampai 20 sesi.');
        }
        $normalizedSessions = [];
        foreach ($sessions as $sessionIndex => $session) {
            if (!is_array($session)) {
                throw new InvalidArgumentException('Isian sesi tidak valid.');
            }
            $name = trim((string) ($session['name'] ?? ''));
            $start = (string) ($session['startTime'] ?? '');
            $end = (string) ($session['endTime'] ?? '');
            if ($name === '' || mb_strlen($name) > 100
                || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start)
                || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end) || $end <= $start) {
                throw new InvalidArgumentException('Isi nama sesi dan jam mulai/selesai yang valid.');
            }
            $quota = tripTemplatePositiveInt($session['quota'] ?? null, 'Kuota sesi');
            foreach ($weekdays as $day) {
                foreach ($occurrences as $nth) {
                    $key = "$day|$nth|$start|$end";
                    if (isset($occupied[$key])) {
                        throw new InvalidArgumentException('Ada aturan dengan hari dan jam sesi yang sama. Pisahkan pilihan hari atau urutannya.');
                    }
                    $occupied[$key] = true;
                }
            }
            $normalizedSessions[] = [
                'code' => 'R' . ($ruleIndex + 1) . 'S' . ($sessionIndex + 1),
                'name' => $name, 'startTime' => $start, 'endTime' => $end, 'quota' => $quota,
            ];
        }
        if ($type === 'daily') {
            $rules[] = ['type' => 'daily', 'sessions' => $normalizedSessions];
            $labels[] = 'Setiap hari';
        } else {
            foreach ($weekdays as $day) {
                foreach ($occurrences as $nth) {
                    $rules[] = ['type' => 'nth_weekday', 'weekday' => $day, 'nth' => $nth, 'sessions' => $normalizedSessions];
                }
            }
            $dayLabel = implode(', ', array_map(static fn(int $day): string => $days[$day], $weekdays));
            $labels[] = $type === 'weekly' ? 'Setiap ' . $dayLabel : $dayLabel . ' ke-' . implode(', ke-', $occurrences);
        }
        $groups[] = [
            'number' => count($groups) + 1,
            'type' => $type,
            'label' => $labels[count($labels) - 1],
            'rules' => array_slice($rules, $firstCompiledRule),
        ];
    }
    $label = implode('; ', $labels);
    return [
        'pattern' => ['rules' => $rules],
        'label' => mb_strlen($label) <= 255 ? $label : count($input) . ' aturan jadwal berulang',
        'groups' => $groups,
    ];
}

function previewManagedTripTemplatePattern(mixed $input, int $year, int $month): array
{
    $period = tripTemplatePeriod($year, $month);
    $normalized = normalizeManagedTripTemplatePattern($input);
    $template = [
        'trip_type' => 'open',
        'template_key' => 'PREVIEW',
        'schedule_pattern_json' => jsonText($normalized['pattern']),
    ];
    // Preview and generation deliberately share the same date/session builder.
    $schedules = buildTripTemplateSchedules($template, $year, $month);
    $start = new DateTimeImmutable($period['startDate']);
    $end = new DateTimeImmutable($period['endDate']);
    $weekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $groups = [];
    foreach ($normalized['groups'] as $group) {
        $dates = [];
        $sessionCount = 0;
        $missing = [];
        foreach ($group['rules'] as $rule) {
            $ruleDates = tripTemplateRuleDates($rule, $start, $end);
            foreach ($ruleDates as $date) $dates[$date->format('Y-m-d')] = true;
            $sessionCount += count($ruleDates) * count($rule['sessions']);
            // A fifth occurrence is implicit in weekly rules. Only explicitly
            // selected monthly occurrences need a notice when absent.
            if (!$ruleDates && $group['type'] === 'monthly') {
                $missing[] = $weekdays[$rule['weekday']] . ' ke-' . $rule['nth'];
            }
        }
        $notice = '';
        if ($missing) {
            $notice = 'Aturan ' . $group['number'] . ': ' . implode(', ', $missing)
                . ' tidak tersedia pada ' . $period['label'] . '.';
            if (!$dates) $notice .= ' Aturan ini tidak menghasilkan tanggal pada bulan contoh ini.';
        }
        $groups[] = [
            'number' => $group['number'], 'label' => $group['label'],
            'dateCount' => count($dates), 'sessionCount' => $sessionCount, 'notice' => $notice,
        ];
    }
    return [
        'period' => $period, 'patternLabel' => $normalized['label'],
        'dateCount' => count(array_unique(array_column($schedules, 'date'))),
        'sessionCount' => count($schedules),
        'totalQuota' => array_sum(array_column($schedules, 'quota')),
        'rules' => $groups, 'schedules' => $schedules,
    ];
}

function validateManagedTripTemplateContent(array $data, bool $private): void
{
    $name = trim((string) ($data['name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 170) {
        throw new InvalidArgumentException('Nama template wajib diisi, maksimal 170 karakter.');
    }
    foreach (['destination', 'description'] as $field) {
        if (!is_array($data[$field] ?? null) || trim((string) ($data[$field]['id'] ?? '')) === '') {
            throw new InvalidArgumentException('Destinasi dan deskripsi Indonesia wajib diisi.');
        }
    }
    foreach (['activities', 'facilities'] as $field) {
        if (!is_array($data[$field]['id'] ?? null) || !$data[$field]['id']) {
            throw new InvalidArgumentException('Aktivitas dan fasilitas Indonesia wajib diisi.');
        }
    }
    if (!is_numeric($data['price'] ?? null) || !is_finite((float) $data['price']) || (float) $data['price'] <= 0) {
        throw new InvalidArgumentException('Harga trip harus lebih dari 0.');
    }
    foreach (['addons', 'privatePackages', 'imageUrls', 'sessions'] as $field) {
        if (isset($data[$field]) && !is_array($data[$field])) {
            throw new InvalidArgumentException('Isian ' . $field . ' tidak valid.');
        }
    }
    foreach (['addons', 'privatePackages', 'sessions'] as $field) {
        foreach ($data[$field] ?? [] as $item) {
            if (!is_array($item)) throw new InvalidArgumentException('Isian ' . $field . ' tidak valid.');
        }
    }
    if (!$private) return;
    $minimum = tripTemplatePositiveInt($data['minParticipants'] ?? null, 'Minimal peserta');
    $maximum = tripTemplatePositiveInt($data['maxParticipants'] ?? null, 'Maksimal peserta');
    if ($minimum > $maximum) throw new InvalidArgumentException('Minimal peserta melebihi maksimal peserta.');
    $sessions = $data['sessions'] ?? [];
    if (!is_array($sessions) || !$sessions || count($sessions) > 20) {
        throw new InvalidArgumentException('Private Trip memerlukan 1 sampai 20 sesi.');
    }
    if (!array_filter($sessions, static fn(array $session): bool => ($session['status'] ?? 'active') !== 'inactive')) {
        throw new InvalidArgumentException('Private Trip memerlukan minimal satu sesi aktif.');
    }
    $sessionKeys = [];
    foreach ($sessions as $session) {
        $start = (string) ($session['startTime'] ?? '');
        $end = (string) ($session['endTime'] ?? '');
        if (trim((string) ($session['name'] ?? '')) === ''
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end) || $end <= $start
            || isset($sessionKeys[$start . '|' . $end])) {
            throw new InvalidArgumentException('Isi sesi Private Trip dengan nama dan jam yang valid serta tidak duplikat.');
        }
        $sessionKeys[$start . '|' . $end] = true;
    }
    if (empty($data['privatePackages'])) {
        $tiers = tripTemplatePositiveInt($data['maxCustomPax'] ?? null, 'Batas tier harga', $maximum);
        for ($pax = 1; $pax <= $tiers; $pax++) {
            $price = $data['pricePerPersonTiers'][$pax] ?? null;
            if (!is_numeric($price) || !is_finite((float) $price) || (float) $price <= 0) {
                throw new InvalidArgumentException('Lengkapi semua tier harga Private Trip.');
            }
        }
    }
}
