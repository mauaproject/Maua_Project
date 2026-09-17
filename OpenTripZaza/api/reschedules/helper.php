<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/helpers.php';

function requireRescheduleUser(PDO $pdo, string $role): array
{
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user || ($user['role'] ?? '') !== $role) {
        jsonError($role === 'admin' ? 'Akses admin diperlukan.' : 'Akses customer diperlukan.', 403);
    }
    return $user;
}

function validRescheduleDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function rescheduleTime(mixed $value): string
{
    $time = trim((string) $value);
    return $time === '' ? '' : substr($time, 0, 5);
}

function rescheduleTextLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function rescheduleFee(array $booking): array
{
    $today = appNow()->format('Y-m-d');
    $date = new DateTimeImmutable((string) $booking['selected_date']);
    $daysUntilTrip = (int) (new DateTimeImmutable($today))->diff($date)->format('%r%a');
    if ($daysUntilTrip <= 0) {
        throw new InvalidArgumentException('Reschedule hanya dapat diajukan sebelum tanggal trip.');
    }
    $base = (float) $booking['total_price'];
    return [
        'baseAmount' => $base,
        'amount' => $daysUntilTrip <= 7 ? round($base * 0.2) : 0,
        'daysUntilTrip' => $daysUntilTrip,
    ];
}

function privateRescheduleSlotTaken(PDO $pdo, int $tripId, int $sessionId, string $date, int $bookingId, ?int $requestId = null): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE trip_id = ? AND session_id = ? AND selected_date = ? AND id <> ?
           AND status IN ('Menunggu Approval','Disetujui','Selesai')"
    );
    $statement->execute([$tripId, $sessionId, $date, $bookingId]);
    if ((int) $statement->fetchColumn() > 0) {
        return true;
    }
    $sql = "SELECT COUNT(*) FROM booking_reschedule_requests r
            INNER JOIN bookings b ON b.id = r.booking_id
            WHERE b.trip_id = ? AND r.requested_session_id = ? AND r.requested_date = ?
              AND b.id <> ? AND b.status = 'Disetujui'
              AND (r.status = 'pending' OR (r.status = 'awaiting_payment' AND r.payment_expires_at > NOW()))";
    $params = [$tripId, $sessionId, $date, $bookingId];
    if ($requestId !== null) {
        $sql .= ' AND r.id <> ?';
        $params[] = $requestId;
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return (int) $statement->fetchColumn() > 0;
}

function mapRescheduleRows(array $rows): array
{
    return array_map(static fn(array $row): array => [
        'id' => (int) $row['id'],
        'bookingId' => (int) $row['booking_id'],
        'bookingCode' => 'MAUA-' . (int) $row['booking_id'],
        'tripId' => (int) $row['trip_id'],
        'tripName' => $row['trip_name'] ?? '',
        'tripType' => $row['trip_type'] ?? '',
        'customerName' => $row['customer_name'] ?? '',
        'customerEmail' => $row['customer_email'] ?? '',
        'customerWhatsapp' => $row['customer_whatsapp'] ?? '',
        'participants' => (int) ($row['participants'] ?? 0),
        'oldScheduleId' => nullableInt($row['old_schedule_id'] ?? null),
        'oldSessionId' => nullableInt($row['old_session_id'] ?? null),
        'oldDate' => $row['old_selected_date'],
        'oldStartTime' => rescheduleTime($row['old_start_time'] ?? ''),
        'oldEndTime' => rescheduleTime($row['old_end_time'] ?? ''),
        'oldSessionName' => $row['old_session_name'] ?? '',
        'requestedScheduleId' => nullableInt($row['requested_schedule_id'] ?? null),
        'requestedSessionId' => nullableInt($row['requested_session_id'] ?? null),
        'requestedDate' => $row['requested_date'],
        'requestedStartTime' => rescheduleTime($row['requested_start_time'] ?? ''),
        'requestedEndTime' => rescheduleTime($row['requested_end_time'] ?? ''),
        'requestedSessionName' => $row['requested_session_name'] ?? '',
        'reason' => $row['reason'],
        'feeBaseAmount' => (float) ($row['fee_base_amount'] ?? 0),
        'feeAmount' => (float) ($row['fee_amount'] ?? 0),
        'paymentProofUrl' => $row['payment_proof_url'] ?? '',
        'paymentSubmittedAt' => $row['payment_submitted_at'] ?? null,
        'paymentExpiresAt' => $row['payment_expires_at'] ?? null,
        'status' => $row['status'],
        'adminNote' => $row['admin_note'] ?? '',
        'reviewedByName' => $row['reviewed_by_name'] ?? '',
        'reviewedAt' => $row['reviewed_at'] ?? null,
        'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at'],
    ], $rows);
}

function rescheduleSelectSql(): string
{
    return "SELECT r.*, b.trip_id, b.trip_type, b.customer_name, b.customer_email,
                   b.customer_whatsapp, b.participants, t.name trip_name,
                   COALESCE(old_schedule.session_name, old_session.name, '') old_session_name,
                   COALESCE(new_schedule.session_name, new_session.name, '') requested_session_name,
                   reviewer.name reviewed_by_name
            FROM booking_reschedule_requests r
            INNER JOIN bookings b ON b.id = r.booking_id
            INNER JOIN trips t ON t.id = b.trip_id
            LEFT JOIN trip_schedules old_schedule ON old_schedule.id = r.old_schedule_id
            LEFT JOIN trip_sessions old_session ON old_session.id = r.old_session_id
            LEFT JOIN trip_schedules new_schedule ON new_schedule.id = r.requested_schedule_id
            LEFT JOIN trip_sessions new_session ON new_session.id = r.requested_session_id
            LEFT JOIN users reviewer ON reviewer.id = r.reviewed_by";
}
