<?php
declare(strict_types=1);

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/invoice-pdf.php';

function reminderEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function reminderDate(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date('d-m-Y', $timestamp) : $date;
}

function reminderMoney(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function reminderLayout(string $title, string $content): string
{
    $brand = reminderEscape(getenv('MAIL_FROM_NAME') ?: 'MAUA Project');
    return '<!doctype html><html><body style="margin:0;background:#f4f1ea;font-family:Arial,sans-serif;color:#26332d">'
        . '<div style="max-width:680px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:#173f35;color:#fff;padding:24px 30px"><strong style="font-size:22px">' . $brand . '</strong></div>'
        . '<div style="padding:30px"><h1 style="font-size:24px;margin:0 0 20px">' . reminderEscape($title) . '</h1>'
        . $content
        . '<p style="margin-top:28px;color:#66736e;font-size:13px">Email ini dikirim otomatis. Silakan hubungi admin jika ada perubahan data perjalanan.</p>'
        . '</div></div></body></html>';
}

function adminContactHtml(): string
{
    $whatsapp = trim((string) getenv('ADMIN_WHATSAPP'));
    $email = trim((string) (getenv('ADMIN_EMAIL') ?: getenv('MAIL_FROM_ADDRESS')));
    $parts = [];
    if ($whatsapp !== '') {
        $waNumber = preg_replace('/\D+/', '', $whatsapp);
        $parts[] = '<a href="https://wa.me/' . reminderEscape($waNumber) . '">' . reminderEscape($whatsapp) . '</a>';
    }
    if ($email !== '') {
        $parts[] = '<a href="mailto:' . reminderEscape($email) . '">' . reminderEscape($email) . '</a>';
    }
    return $parts ? implode(' &middot; ', $parts) : '-';
}

function fetchReminderInvoice(PDO $pdo, array $booking): array
{
    $addonStatement = $pdo->prepare(
        "SELECT COALESCE(ta.name, a.label, ba.addon_id, 'Add-on') name,
                ba.quantity, ba.price
         FROM booking_addons ba
         LEFT JOIN trip_addons ta ON ta.id = ba.trip_addon_id
         LEFT JOIN addons a ON a.id = ba.addon_id
         WHERE ba.booking_id = ?
         ORDER BY ba.id"
    );
    $addonStatement->execute([(int) $booking['id']]);
    $addons = array_map(static function (array $addon): array {
        $quantity = max(1, (int) $addon['quantity']);
        return [
            'name' => (string) $addon['name'],
            'quantity' => $quantity,
            'price' => (float) $addon['price'],
            'total' => $quantity * (float) $addon['price'],
        ];
    }, $addonStatement->fetchAll());

    $paymentStatement = $pdo->prepare(
        'SELECT amount, payment_method, payment_status, submitted_at
         FROM payments WHERE booking_id = ? ORDER BY id'
    );
    $paymentStatement->execute([(int) $booking['id']]);
    $payments = $paymentStatement->fetchAll();
    $paidAmount = array_sum(array_map(static fn(array $payment): float => (float) $payment['amount'], $payments));
    $addonTotal = array_sum(array_column($addons, 'total'));
    $tripSubtotal = (int) $booking['participants'] * (float) $booking['price_per_person'];
    $subtotal = (float) $booking['total_price'];
    if ($subtotal <= 0) {
        $subtotal = $tripSubtotal + $addonTotal;
    }
    $paymentLines = array_map(static fn(array $payment): string => sprintf(
        '%s - %s (%s), %s',
        reminderMoney((float) $payment['amount']),
        (string) $payment['payment_method'],
        (string) $payment['payment_status'],
        reminderDate((string) $payment['submitted_at'])
    ), $payments);
    if (!$paymentLines) {
        $paymentLines[] = 'Belum ada pembayaran yang tercatat.';
    }

    return [
        'bookingId' => (int) $booking['id'],
        'invoiceDate' => date('d-m-Y'),
        'tripDate' => reminderDate((string) $booking['selected_date']),
        'customerName' => (string) $booking['customer_name'],
        'customerEmail' => (string) $booking['customer_email'],
        'tripName' => (string) $booking['trip_name'],
        'participants' => (int) $booking['participants'],
        'pricePerPerson' => (float) $booking['price_per_person'],
        'tripSubtotal' => $tripSubtotal,
        'addons' => $addons,
        'subtotal' => $subtotal,
        'paidAmount' => $paidAmount,
        'balanceDue' => max(0, $subtotal - $paidAmount),
        'paymentLines' => $paymentLines,
        'paymentInstructions' => trim((string) getenv('PAYMENT_DETAILS')),
    ];
}

function invoiceHtml(array $invoice): string
{
    $items = '<tr><td style="padding:10px;border-bottom:1px solid #ddd">'
        . reminderEscape($invoice['tripName']) . '<br><small>' . $invoice['participants'] . ' peserta</small></td>'
        . '<td style="padding:10px;text-align:right;border-bottom:1px solid #ddd">' . reminderMoney($invoice['tripSubtotal']) . '</td></tr>';
    foreach ($invoice['addons'] as $addon) {
        $items .= '<tr><td style="padding:10px;border-bottom:1px solid #ddd">Add-on: ' . reminderEscape($addon['name']) . '</td>'
            . '<td style="padding:10px;text-align:right;border-bottom:1px solid #ddd">' . reminderMoney($addon['total']) . '</td></tr>';
    }
    $payments = '<ul>';
    foreach ($invoice['paymentLines'] as $line) {
        $payments .= '<li>' . reminderEscape($line) . '</li>';
    }
    $payments .= '</ul>';
    if ($invoice['paymentInstructions'] !== '') {
        $payments .= '<p>' . nl2br(reminderEscape($invoice['paymentInstructions'])) . '</p>';
    }
    return '<div style="border:1px solid #ddd;border-radius:10px;padding:20px">'
        . '<p><strong>Invoice #' . $invoice['bookingId'] . '</strong><br>Invoice date: ' . $invoice['invoiceDate']
        . '<br>Trip date: ' . $invoice['tripDate'] . '</p>'
        . '<p><strong>Invoice to</strong><br>' . reminderEscape($invoice['customerName']) . '<br>'
        . reminderEscape($invoice['customerEmail']) . '</p>'
        . '<table style="width:100%;border-collapse:collapse">' . $items . '</table>'
        . '<p style="text-align:right">Subtotal: <strong>' . reminderMoney($invoice['subtotal']) . '</strong><br>'
        . 'DP / pembayaran tercatat: <strong>' . reminderMoney($invoice['paidAmount']) . '</strong><br>'
        . 'Sisa pembayaran: <strong>' . reminderMoney($invoice['balanceDue']) . '</strong></p>'
        . '<h3>Payment details</h3>' . $payments . '</div>';
}

function buildReminderMessage(PDO $pdo, array $booking, string $type): array
{
    $name = reminderEscape($booking['customer_name']);
    $tripName = reminderEscape($booking['trip_name']);
    $tripDate = reminderDate((string) $booking['selected_date']);
    $participants = (int) $booking['participants'];

    if ($type === 'H7') {
        $content = "<p>Halo <strong>{$name}</strong>, trip <strong>{$tripName}</strong> akan berlangsung 7 hari lagi.</p>"
            . "<ul><li>Tanggal trip: {$tripDate}</li><li>Jumlah peserta: {$participants}</li></ul>"
            . '<p>Ada pertanyaan? Hubungi admin: ' . adminContactHtml() . '</p>';
        return ['subject' => "Pengingat H-7: {$booking['trip_name']}", 'html' => reminderLayout('Trip berlangsung 7 hari lagi', $content), 'attachments' => []];
    }

    if ($type === 'H1') {
        $invoice = fetchReminderInvoice($pdo, $booking);
        $content = "<p>Halo <strong>{$name}</strong>, besok adalah jadwal keberangkatan untuk <strong>{$tripName}</strong>.</p>"
            . '<p>Invoice perjalanan tersedia di bawah ini dan terlampir dalam format PDF.</p>'
            . invoiceHtml($invoice)
            . '<p>Ada pertanyaan? Hubungi admin: ' . adminContactHtml() . '</p>';
        return [
            'subject' => "Pengingat keberangkatan & invoice: {$booking['trip_name']}",
            'html' => reminderLayout('Pengingat H-1 dan invoice', $content),
            'attachments' => [[
                'filename' => 'invoice-MAUA-' . $invoice['bookingId'] . '.pdf',
                'contentType' => 'application/pdf',
                'content' => createInvoicePdf($invoice),
            ]],
        ];
    }

    $reviewUrl = trim((string) getenv('REVIEW_URL'));
    $reviewAction = $reviewUrl !== ''
        ? '<p><a style="display:inline-block;background:#173f35;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none" href="'
            . reminderEscape($reviewUrl) . '">Berikan review</a></p>'
        : '<p>Balas email ini jika Anda ingin membagikan review atau masukan.</p>';
    $content = "<p>Halo <strong>{$name}</strong>, terima kasih sudah mengikuti <strong>{$tripName}</strong>.</p>"
        . '<p>Semoga perjalanannya menyenangkan. Cerita dan masukan Anda sangat berarti bagi kami.</p>' . $reviewAction;
    return ['subject' => "Terima kasih telah mengikuti {$booking['trip_name']}", 'html' => reminderLayout('Terima kasih!', $content), 'attachments' => []];
}

function claimReminder(PDO $pdo, int $bookingId, string $type, string $email): bool
{
    $insert = $pdo->prepare(
        "INSERT IGNORE INTO reminder_logs
         (booking_id, reminder_type, email_to, status, attempts)
         VALUES (?,?,?,'processing',1)"
    );
    $insert->execute([$bookingId, $type, $email]);
    if ($insert->rowCount() === 1) {
        return true;
    }
    $retry = $pdo->prepare(
        "UPDATE reminder_logs
         SET status='processing', email_to=?, error_message=NULL, attempts=attempts+1, updated_at=CURRENT_TIMESTAMP
         WHERE booking_id=? AND reminder_type=?
           AND (status='failed' OR (status='processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)))"
    );
    $retry->execute([$email, $bookingId, $type]);
    return $retry->rowCount() === 1;
}

function runDailyReminders(PDO $pdo): array
{
    $timezone = trim((string) (getenv('APP_TIMEZONE') ?: 'Asia/Jakarta'));
    date_default_timezone_set($timezone);
    $today = new DateTimeImmutable('today');
    $dates = [
        'H7' => $today->modify('+7 days')->format('Y-m-d'),
        'H1' => $today->modify('+1 day')->format('Y-m-d'),
        'HPLUS1' => $today->modify('-1 day')->format('Y-m-d'),
    ];

    $pdo->exec(
        "UPDATE bookings
         SET archived_at=COALESCE(archived_at, NOW())
         WHERE visible_until IS NOT NULL AND visible_until < CURDATE()"
    );
    $pdo->exec(
        "UPDATE trip_schedules
         SET archived_at=COALESCE(archived_at, NOW()),
             status=CASE WHEN status='inactive' THEN status ELSE 'inactive' END
         WHERE visible_until IS NOT NULL AND visible_until < CURDATE()"
    );

    $candidate = $pdo->prepare(
        "SELECT b.*, t.name trip_name
         FROM bookings b
         INNER JOIN trips t ON t.id = b.trip_id
         WHERE b.status IN ('Disetujui','Selesai')
           AND b.selected_date = ?
           AND b.customer_email <> ''"
    );
    $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'archived' => true, 'details' => []];

    foreach ($dates as $type => $targetDate) {
        $candidate->execute([$targetDate]);
        foreach ($candidate->fetchAll() as $booking) {
            $bookingId = (int) $booking['id'];
            $email = strtolower(trim((string) $booking['customer_email']));
            if (!claimReminder($pdo, $bookingId, $type, $email)) {
                $result['skipped']++;
                continue;
            }
            try {
                $message = buildReminderMessage($pdo, $booking, $type);
                sendSmtpMail($email, $message['subject'], $message['html'], $message['attachments']);
                $success = $pdo->prepare(
                    "UPDATE reminder_logs
                     SET status='success', sent_at=NOW(), error_message=NULL
                     WHERE booking_id=? AND reminder_type=?"
                );
                $success->execute([$bookingId, $type]);
                $result['sent']++;
                $result['details'][] = ['bookingId' => $bookingId, 'type' => $type, 'status' => 'success'];
            } catch (Throwable $exception) {
                $error = substr($exception->getMessage(), 0, 2000);
                $failed = $pdo->prepare(
                    "UPDATE reminder_logs
                     SET status='failed', error_message=?
                     WHERE booking_id=? AND reminder_type=?"
                );
                $failed->execute([$error, $bookingId, $type]);
                $result['failed']++;
                $result['details'][] = ['bookingId' => $bookingId, 'type' => $type, 'status' => 'failed', 'error' => $error];
            }
        }
    }
    return $result;
}
