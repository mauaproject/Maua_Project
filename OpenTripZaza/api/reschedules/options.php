<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $user = requireRescheduleUser($pdo, 'customer');
    $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
    if (!$bookingId) {
        throw new InvalidArgumentException('Booking tidak valid.');
    }
    $statement = $pdo->prepare(
        "SELECT b.*, t.name trip_name, t.available_start_date, t.available_end_date,
                t.private_booking_mode
         FROM bookings b INNER JOIN trips t ON t.id = b.trip_id
         WHERE b.id = ? AND b.user_id = ? LIMIT 1"
    );
    $statement->execute([$bookingId, (int) $user['id']]);
    $booking = $statement->fetch();
    if (!$booking) {
        jsonError('Booking tidak ditemukan.', 404);
    }
    if (empty($booking['selected_date']) || $booking['status'] !== 'Disetujui' || scheduledEndAt((string) $booking['selected_date'], $booking['end_time']) <= appNow()) {
        throw new InvalidArgumentException('Hanya booking aktif yang sudah disetujui yang dapat di-reschedule.');
    }
    $pending = $pdo->prepare("SELECT COUNT(*) FROM booking_reschedule_requests WHERE booking_id = ? AND status = 'pending'");
    $pending->execute([$bookingId]);
    if ((int) $pending->fetchColumn() > 0) {
        throw new InvalidArgumentException('Booking ini sudah memiliki pengajuan reschedule yang menunggu persetujuan.');
    }

    $response = [
        'bookingId' => (int) $booking['id'],
        'tripId' => (int) $booking['trip_id'],
        'tripName' => $booking['trip_name'],
        'tripType' => $booking['trip_type'],
        'participants' => (int) $booking['participants'],
        'current' => [
            'date' => $booking['selected_date'],
            'startTime' => rescheduleTime($booking['start_time']),
            'endTime' => rescheduleTime($booking['end_time']),
            'scheduleId' => nullableInt($booking['schedule_id']),
            'sessionId' => nullableInt($booking['session_id']),
        ],
    ];

    if ($booking['trip_type'] === 'open') {
        $schedules = $pdo->prepare(
            "SELECT ts.*,
                    COALESCE((SELECT SUM(b2.participants) FROM bookings b2
                              WHERE b2.schedule_id = ts.id
                                AND b2.status IN ('Menunggu Approval','Disetujui','Selesai')), 0) reserved
             FROM trip_schedules ts
             WHERE ts.trip_id = ?
               AND ts.status IN ('active','full')
               AND ts.archived_at IS NULL
               AND TIMESTAMP(ts.schedule_date, COALESCE(ts.end_time, '23:59:59')) > NOW()
             ORDER BY ts.schedule_date, ts.start_time, ts.id"
        );
        $schedules->execute([(int) $booking['trip_id']]);
        $response['schedules'] = array_map(static function (array $schedule) use ($booking): array {
            $remaining = max(0, (int) $schedule['quota'] - (int) $schedule['reserved']);
            $isCurrent = (int) $schedule['id'] === (int) $booking['schedule_id'];
            return [
                'id' => (int) $schedule['id'],
                'code' => $schedule['schedule_code'],
                'name' => $schedule['session_name'] ?: 'Sesi 1',
                'date' => $schedule['schedule_date'],
                'startTime' => rescheduleTime($schedule['start_time']),
                'endTime' => rescheduleTime($schedule['end_time']),
                'quota' => (int) $schedule['quota'],
                'remaining' => $remaining,
                'isCurrent' => $isCurrent,
                'isSelectable' => !$isCurrent && $schedule['status'] === 'active' && $remaining >= (int) $booking['participants'],
            ];
        }, $schedules->fetchAll());
    } else {
        $sessions = $pdo->prepare(
            "SELECT id, session_code, name, start_time, end_time, status
             FROM trip_sessions WHERE trip_id = ? AND status = 'active'
             ORDER BY start_time, id"
        );
        $sessions->execute([(int) $booking['trip_id']]);
        $response['availableStartDate'] = max($booking['available_start_date'] ?: $booking['selected_date'], appNow()->format('Y-m-d'));
        $response['availableEndDate'] = $booking['available_end_date'];
        $response['privateBookingMode'] = $booking['private_booking_mode'] ?: 'exclusive';
        $response['sessions'] = array_map(static fn(array $session): array => [
            'id' => (int) $session['id'],
            'code' => $session['session_code'],
            'name' => $session['name'] ?: 'Sesi',
            'startTime' => rescheduleTime($session['start_time']),
            'endTime' => rescheduleTime($session['end_time']),
        ], $sessions->fetchAll());
        $blocked = $pdo->prepare(
            "SELECT session_id, selected_date FROM bookings
             WHERE trip_id = ? AND id <> ?
               AND status IN ('Menunggu Approval','Disetujui','Selesai')"
        );
        $blocked->execute([(int) $booking['trip_id'], $bookingId]);
        $response['blockedSlots'] = array_map(static fn(array $row): array => [
            'sessionId' => (int) $row['session_id'],
            'date' => $row['selected_date'],
        ], $booking['private_booking_mode'] === 'shared' ? [] : $blocked->fetchAll());
    }
    jsonSuccess($response);
});
