<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['tripId']);
    $tripId = (int) $data['tripId'];
    $scheduleId = nullableInt($data['scheduleId'] ?? null);
    $sessionId = nullableInt($data['sessionId'] ?? null);
    $selectedDate = trim((string) ($data['selectedDate'] ?? ''));
    $driveLinkUrl = nullableUrl($data['driveLinkUrl'] ?? null, 'Link drive harus berupa URL yang valid.');

    if (!$scheduleId && !$sessionId) {
        throw new InvalidArgumentException('Pilih jadwal atau sesi yang akan diberi link drive.');
    }

    $tripStatement = $pdo->prepare('SELECT id, include_drive_link FROM trips WHERE id = ? LIMIT 1');
    $tripStatement->execute([$tripId]);
    $trip = $tripStatement->fetch();
    if (!$trip) {
        throw new InvalidArgumentException('Trip tidak ditemukan.');
    }
    if (empty($trip['include_drive_link'])) {
        throw new InvalidArgumentException('Paket trip ini belum mengaktifkan fasilitas link drive bawaan.');
    }

    if ($scheduleId) {
        $statement = $pdo->prepare('UPDATE trip_schedules SET drive_link_url = ? WHERE id = ? AND trip_id = ?');
        $statement->execute([$driveLinkUrl, $scheduleId, $tripId]);
        if ($driveLinkUrl) {
            $upsert = $pdo->prepare(
                'INSERT INTO trip_documentation_links (trip_id, schedule_id, session_id, schedule_date, drive_link_url)
                 VALUES (?,?,NULL,NULL,?)
                 ON DUPLICATE KEY UPDATE drive_link_url = VALUES(drive_link_url), updated_at = CURRENT_TIMESTAMP'
            );
            $upsert->execute([$tripId, $scheduleId, $driveLinkUrl]);
        } else {
            $pdo->prepare('DELETE FROM trip_documentation_links WHERE schedule_id = ?')->execute([$scheduleId]);
        }
    } else {
        if ($selectedDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            throw new InvalidArgumentException('Tanggal dokumentasi private trip wajib diisi.');
        }
        $statement = $pdo->prepare('SELECT id FROM trip_sessions WHERE id = ? AND trip_id = ?');
        $statement->execute([$sessionId, $tripId]);
        if (!$statement->fetch()) {
            throw new InvalidArgumentException('Jadwal atau sesi tidak ditemukan.');
        }
        if ($driveLinkUrl) {
            $upsert = $pdo->prepare(
                'INSERT INTO trip_documentation_links (trip_id, schedule_id, session_id, schedule_date, drive_link_url)
                 VALUES (?,NULL,?,?,?)
                 ON DUPLICATE KEY UPDATE drive_link_url = VALUES(drive_link_url), updated_at = CURRENT_TIMESTAMP'
            );
            $upsert->execute([$tripId, $sessionId, $selectedDate, $driveLinkUrl]);
        } else {
            $pdo->prepare('DELETE FROM trip_documentation_links WHERE trip_id = ? AND session_id = ? AND schedule_date = ?')
                ->execute([$tripId, $sessionId, $selectedDate]);
        }
    }

    if ($scheduleId && $statement->rowCount() === 0) {
        $table = $scheduleId ? 'trip_schedules' : 'trip_sessions';
        $id = $scheduleId ?: $sessionId;
        $exists = $pdo->prepare("SELECT id FROM {$table} WHERE id = ? AND trip_id = ?");
        $exists->execute([$id, $tripId]);
        if (!$exists->fetch()) {
            throw new InvalidArgumentException('Jadwal atau sesi tidak ditemukan.');
        }
    }

    $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE id = ? LIMIT 1');
    $tripStatement->execute([$tripId]);
    jsonSuccess(mapTrip($pdo, $tripStatement->fetch()));
});
