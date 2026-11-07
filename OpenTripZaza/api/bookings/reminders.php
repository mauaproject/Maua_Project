<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/reminders.php';
requireMethod(['GET', 'POST']);

const REMINDER_TYPE_META = [
    'H7' => ['label' => 'H-7'],
    'H1' => ['label' => 'H-1'],
    'HPLUS1' => ['label' => 'H+2'],
];

function reminderWindow(string $type): array
{
    $today = new DateTimeImmutable('today');
    return match ($type) {
        // H7 dikirim saat trip berada 2-7 hari ke depan.
        'H7' => [$today->modify('+2 days'), $today->modify('+7 days')],
        'H1' => [$today->modify('+1 day'), $today->modify('+1 day')],
        // Email review dikirim 2 hari setelah trip (label H+2), key mengikuti enum di reminder_logs.
        'HPLUS1' => [$today->modify('-2 days'), $today->modify('-2 days')],
        default => throw new InvalidArgumentException('Tipe reminder tidak dikenal.'),
    };
}

function reminderLogState(?array $log): ?array
{
    if (!$log) {
        return null;
    }
    return [
        'status' => (string) $log['status'],
        'sentAt' => $log['sent_at'],
        'error' => $log['error_message'],
        'attempts' => (int) $log['attempts'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    runEndpoint(function (PDO $pdo): void {
        $tripId = (int) ($_GET['trip_id'] ?? 0);
        $scheduleId = trim((string) ($_GET['schedule_id'] ?? ''));
        $sessionId = trim((string) ($_GET['session_id'] ?? ''));
        if ($tripId <= 0) {
            throw new InvalidArgumentException('Trip tidak valid.');
        }

        $sql = "SELECT b.id, b.customer_name, b.customer_email, b.status, b.selected_date
                FROM bookings b
                WHERE b.trip_id = ? AND b.status IN ('Disetujui','Selesai')";
        $params = [$tripId];
        if ($scheduleId !== '') {
            $sql .= ' AND b.schedule_id = ?';
            $params[] = (int) $scheduleId;
        }
        if ($sessionId !== '') {
            $sql .= ' AND b.session_id = ?';
            $params[] = (int) $sessionId;
        }
        $sql .= ' ORDER BY b.selected_date, b.id';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $bookings = $statement->fetchAll();
        if (!$bookings) {
            jsonSuccess(['items' => [], 'serverDate' => appNow()->format('Y-m-d')]);
        }

        $bookingIds = array_map(static fn(array $booking): int => (int) $booking['id'], $bookings);
        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $logStatement = $pdo->prepare(
            "SELECT booking_id, reminder_type, status, sent_at, error_message, attempts
             FROM reminder_logs WHERE booking_id IN ($placeholders)"
        );
        $logStatement->execute($bookingIds);
        $logs = [];
        foreach ($logStatement->fetchAll() as $log) {
            $logs[(int) $log['booking_id']][(string) $log['reminder_type']] = $log;
        }

        $today = new DateTimeImmutable('today');
        $items = [];
        foreach ($bookings as $booking) {
            $bookingId = (int) $booking['id'];
            $selectedDate = new DateTimeImmutable((string) $booking['selected_date']);
            $reminders = [];
            foreach (REMINDER_TYPE_META as $type => $meta) {
                [$windowStart, $windowEnd] = reminderWindow($type);
                $log = $logs[$bookingId][$type] ?? null;
                if ($log && $log['status'] === 'success') {
                    $state = 'success';
                } elseif ($log && $log['status'] === 'failed') {
                    $state = 'failed';
                } elseif ($log && $log['status'] === 'processing') {
                    $state = 'processing';
                } elseif ($selectedDate > $windowEnd) {
                    $state = 'upcoming';
                } elseif ($selectedDate < $windowStart) {
                    // Jendela pengiriman sudah lewat tanpa ada email yang pernah diklaim.
                    $state = 'missed';
                } else {
                    // Di dalam jendela, menunggu cron berikutnya (mis. booking baru disetujui).
                    $state = 'pending';
                }
                $reminders[] = [
                    'type' => $type,
                    'label' => $meta['label'],
                    'state' => $state,
                    'log' => reminderLogState($log),
                ];
            }
            $items[] = [
                'id' => $bookingId,
                'name' => (string) $booking['customer_name'],
                'email' => (string) $booking['customer_email'],
                'status' => (string) $booking['status'],
                'selectedDate' => (string) $booking['selected_date'],
                'reminders' => $reminders,
            ];
        }
        jsonSuccess(['items' => $items, 'serverDate' => $today->format('Y-m-d')]);
    });
}

// POST: kirim ulang reminder secara manual.
runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['bookingId', 'type']);
    $bookingId = (int) $data['bookingId'];
    $type = strtoupper(trim((string) $data['type']));
    if (!array_key_exists($type, REMINDER_TYPE_META)) {
        throw new InvalidArgumentException('Tipe reminder tidak dikenal.');
    }

    $statement = $pdo->prepare(
        "SELECT b.*, t.name trip_name, t.h7_reminder_subject, t.h7_reminder_body
         FROM bookings b
         INNER JOIN trips t ON t.id = b.trip_id
         WHERE b.id = ?"
    );
    $statement->execute([$bookingId]);
    $booking = $statement->fetch();
    if (!$booking) {
        jsonError('Booking tidak ditemukan.', 404);
    }
    if (!in_array($booking['status'], ['Disetujui', 'Selesai'], true)) {
        throw new InvalidArgumentException('Reminder hanya bisa dikirim untuk booking yang disetujui.');
    }
    $email = strtolower(trim((string) $booking['customer_email']));
    if ($email === '') {
        throw new InvalidArgumentException('Booking ini tidak memiliki email customer.');
    }

    // Paksa klaim ulang: pengiriman manual boleh mengirim lagi walau sebelumnya sukses/gagal.
    $pdo->prepare(
        "INSERT INTO reminder_logs (booking_id, reminder_type, email_to, status, attempts)
         VALUES (?, ?, ?, 'processing', 1)
         ON DUPLICATE KEY UPDATE
            status = 'processing', email_to = VALUES(email_to), error_message = NULL,
            attempts = attempts + 1, updated_at = CURRENT_TIMESTAMP"
    )->execute([$bookingId, $type, $email]);

    try {
        $message = buildReminderMessage($pdo, $booking, $type);
        sendSmtpMail($email, $message['subject'], $message['html'], $message['attachments']);
        $pdo->prepare(
            "UPDATE reminder_logs SET status='success', sent_at=NOW(), error_message=NULL
             WHERE booking_id = ? AND reminder_type = ?"
        )->execute([$bookingId, $type]);
        jsonSuccess([
            'bookingId' => $bookingId,
            'type' => $type,
            'email' => $email,
            'message' => 'Reminder berhasil dikirim ke ' . $email,
        ]);
    } catch (Throwable $exception) {
        $error = substr($exception->getMessage(), 0, 2000);
        $pdo->prepare(
            "UPDATE reminder_logs SET status='failed', error_message=?
             WHERE booking_id = ? AND reminder_type = ?"
        )->execute([$error, $bookingId, $type]);
        throw new RuntimeException('Gagal mengirim reminder: ' . $error);
    }
});
