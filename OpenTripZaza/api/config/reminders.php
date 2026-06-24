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

function h7PlaceholderValues(array $booking): array
{
    $brand = trim((string) (getenv('MAIL_FROM_NAME') ?: 'MAUA Project'));
    $admin = trim((string) (getenv('ADMIN_NAME') ?: $brand));
    $today = new DateTimeImmutable('today');
    $tripDate = new DateTimeImmutable((string) $booking['selected_date']);
    $remainingDays = max(0, (int) $today->diff($tripDate)->format('%r%a'));
    return [
        '{nama_customer}' => (string) $booking['customer_name'],
        '{nama_trip}' => (string) $booking['trip_name'],
        '{tanggal_trip}' => reminderDate((string) $booking['selected_date']),
        '{jam_trip}' => reminderTimeRange($booking['start_time'] ?? null, $booking['end_time'] ?? null),
        '{jumlah_peserta}' => (string) ((int) $booking['participants']),
        '{sisa_hari}' => (string) $remainingDays,
        '{nama_admin}' => $admin,
        '{nama_brand}' => $brand,
        '{nama_admin / nama_brand}' => $admin !== '' ? $admin : $brand,
    ];
}

function reminderTimeRange(mixed $startTime, mixed $endTime): string
{
    $start = $startTime ? substr((string) $startTime, 0, 5) : '';
    $end = $endTime ? substr((string) $endTime, 0, 5) : '';
    if ($start !== '' && $end !== '') {
        return $start . ' - ' . $end . ' WIB';
    }
    return $start !== '' ? $start . ' WIB' : 'Akan diinformasikan admin';
}

function renderH7Template(string $template, array $booking): string
{
    $template = str_ireplace('7 hari lagi', '{sisa_hari} hari lagi', $template);
    return strtr($template, h7PlaceholderValues($booking));
}

function defaultH7Subject(): string
{
    return 'Pengingat H-7: {nama_trip}';
}

function defaultH7Body(): string
{
    return "Halo {nama_customer},\n\n"
        . "Trip {nama_trip} akan berlangsung {sisa_hari} hari lagi.\n\n"
        . "Tanggal trip: {tanggal_trip}\n"
        . "Jam trip: {jam_trip}\n"
        . "Jumlah peserta: {jumlah_peserta}";
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
    $addonTotal = array_sum(array_column($addons, 'total'));
    $tripSubtotal = (float) ($booking['selected_package_subtotal'] ?? 0);
    if ($tripSubtotal <= 0) {
        $tripSubtotal = (int) $booking['participants'] * (float) $booking['price_per_person'];
    }
    $subtotal = (float) $booking['total_price'];
    if ($subtotal <= 0) {
        $subtotal = $tripSubtotal + $addonTotal;
    }
    $paymentType = (string) ($booking['payment_type'] ?? '');
    if ($paymentType === 'full') {
        $paidAmount = $subtotal;
    } elseif ($paymentType === 'dp') {
        $paidAmount = (float) ($booking['paid_amount'] ?? $booking['required_payment_amount'] ?? round($subtotal * 0.5));
    } else {
        $paidAmount = array_sum(array_map(static fn(array $payment): float => (float) $payment['amount'], $payments));
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
        'packageName' => (string) ($booking['selected_package_name'] ?? ''),
        'participants' => (int) $booking['participants'],
        'pricePerPerson' => (float) $booking['price_per_person'],
        'tripSubtotal' => $tripSubtotal,
        'addons' => $addons,
        'subtotal' => $subtotal,
        'paidAmount' => $paidAmount,
        'balanceDue' => max(0, $subtotal - $paidAmount),
        'paymentType' => $paymentType,
        'paymentLines' => $paymentLines,
        'paymentInstructions' => trim((string) getenv('PAYMENT_DETAILS')),
    ];
}

function invoiceHtml(array $invoice): string
{
    $items = '<tr><td style="padding:10px;border-bottom:1px solid #ddd">'
        . reminderEscape($invoice['tripName'])
        . ($invoice['packageName'] !== '' ? '<br><small>Paket: ' . reminderEscape($invoice['packageName']) . '</small>' : '')
        . '<br><small>' . $invoice['participants'] . ' peserta</small></td>'
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
        . 'Pembayaran ' . ($invoice['paymentType'] === 'full' ? 'Lunas' : 'DP') . ': <strong>' . reminderMoney($invoice['paidAmount']) . '</strong><br>'
        . 'Sisa pembayaran: <strong>' . reminderMoney($invoice['balanceDue']) . '</strong></p>'
        . '<h3>Payment details</h3>' . $payments . '</div>';
}

function h1ReminderHtml(array $booking, array $invoice): string
{
    $customerName = reminderEscape($booking['customer_name']);
    $tripName = reminderEscape($booking['trip_name']);
    $tripDate = reminderEscape(reminderDate((string) $booking['selected_date']));
    $paidAmount = reminderMoney((float) $invoice['paidAmount']);
    $balanceDue = reminderMoney((float) $invoice['balanceDue']);

    return '<div style="line-height:1.65">'
        . '<p>Hi, Kak <strong>' . $customerName . '</strong>.</p>'
        . '<p>Tidak terasa, pengalaman seru bersama Maua Project sudah semakin dekat. '
        . 'Terima kasih telah mempercayakan petualangan Anda kepada kami. Sebagai pengingat, '
        . 'berikut kami sampaikan informasi reservasi yang masih memiliki kekurangan pembayaran:</p>'
        . '<p style="margin-bottom:8px">&#128204; <strong>Kegiatan:</strong> ' . $tripName . '<br>'
        . '&#128197; <strong>Tanggal:</strong> ' . $tripDate . '</p>'
        . '<p style="margin-bottom:8px"><strong>Rincian Pembayaran (terlampir invoice)</strong></p>'
        . '<p style="margin-top:0">&#9989; <strong>DP Diterima:</strong> ' . $paidAmount . '<br>'
        . '&#128179; <strong>Sisa Pelunasan:</strong> ' . $balanceDue . '</p>'
        . '<p><strong>Transfer ke:</strong><br>'
        . 'BCA<br>'
        . '4561504789<br>'
        . 'a.n. Zakkiatuz Zahrolazizah</p>'
        . '<p style="margin-bottom:8px">&#128221; <strong>Notes:</strong></p>'
        . '<ul style="margin-top:0;padding-left:22px">'
        . '<li>Mohon melakukan pelunasan sebelum kegiatan dimulai agar proses registrasi pada hari pelaksanaan dapat berjalan lebih lancar dan nyaman.</li>'
        . '<li>Setelah melakukan pembayaran, mohon membalas email ini dengan menyertakan bukti transfer untuk membantu proses verifikasi.</li>'
        . '<li>Pelunasan dapat dilakukan sebelum hari kegiatan maupun pada hari pelaksanaan sebelum aktivitas dimulai.</li>'
        . '<li>Mohon tidak melakukan transfer pada pukul 22.00–03.00 WIB untuk menghindari keterlambatan verifikasi.</li>'
        . '<li>Penambahan layanan dokumentasi hanya dapat dilakukan maksimal H-1 sebelum kegiatan.</li>'
        . '<li>Reservasi yang telah dilakukan bersifat non-refundable. Reschedule tersedia sesuai syarat dan ketentuan yang berlaku.</li>'
        . '</ul>'
        . '<p>Kami tidak sabar untuk menyambut <strong>' . $customerName . '</strong> dalam petualangan bersama Maua Project. '
        . 'Sampai jumpa esok hari dan mari ciptakan pengalaman yang aman, seru, dan berkesan bersama&#129293;&#10024;</p>'
        . '<p>Salam hangat,<br><strong>Maua Project</strong></p>'
        . '</div>';
}

function hPlus1ReminderHtml(array $booking): string
{
    $customerName = reminderEscape($booking['customer_name']);
    $tripName = reminderEscape($booking['trip_name']);
    $accountUrl = trim((string) (getenv('CUSTOMER_ACCOUNT_URL') ?: 'https://mauaproject.com/akun'));
    $reviewUrl = trim((string) getenv('REVIEW_URL'));
    $accountLink = '<a href="' . reminderEscape($accountUrl) . '" style="color:#173f35;font-weight:bold">'
        . reminderEscape(preg_replace('#^https?://#', '', $accountUrl) ?? $accountUrl)
        . '</a>';
    $reviewLink = $reviewUrl !== ''
        ? '<a href="' . reminderEscape($reviewUrl) . '" style="display:inline-block;background:#173f35;color:#fff;'
            . 'padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:bold">Berikan Review Anda</a>'
        : '<strong>Silakan membalas email ini untuk memberikan ulasan Anda.</strong>';

    return '<div style="line-height:1.65">'
        . '<p>Hi, Kak <strong>' . $customerName . '</strong>.</p>'
        . '<p>Terima kasih telah menjadi bagian dari perjalanan bersama Maua Project. Kami merasa senang dapat berbagi '
        . 'pengalaman dan petualangan dalam kegiatan <strong>' . $tripName . '</strong> bersama Kakak.</p>'
        . '<p>Sebagai kenang-kenangan dari perjalanan tersebut, dokumentasi kegiatan telah kami unggah dan dapat diakses '
        . 'melalui tautan berikut:<br>' . $accountLink . '</p>'
        . '<p>Dokumentasi dapat diakses selama 7 (tujuh) hari sejak email ini dikirimkan. Setelah periode tersebut berakhir, '
        . 'file akan kami arsipkan. Apabila berkenan, kami sarankan untuk mengunduh seluruh dokumentasi sebelum batas waktu '
        . 'akses berakhir.</p>'
        . '<p>Kami juga mohon izin apabila beberapa foto atau video dari kegiatan kemarin digunakan sebagai materi promosi '
        . 'dan dokumentasi di media sosial maupun platform resmi Maua Project. Apabila terdapat foto atau video yang tidak '
        . 'berkenan untuk dipublikasikan, silakan membalas email ini atau menghubungi admin kami.</p>'
        . '<p>Kiranya berkenan mengunggah momen selama kegiatan di media sosial, kami akan sangat senang apabila Kakak '
        . 'berkenan menandai atau mengajak kolaborasi akun <strong>@mauaproject</strong>. Cerita dan pengalaman yang Kakak '
        . 'bagikan akan sangat berarti bagi kami.</p>'
        . '<p>Selain itu, kami juga ingin meminta sedikit waktu Anda untuk memberikan ulasan mengenai pengalaman bersama '
        . 'Maua Project.</p>'
        . '<p>&#11088; <strong>Berikan Review Anda di:</strong><br>' . $reviewLink . '</p>'
        . '<p>Atas kepercayaan yang telah diberikan, kami mengucapkan terima kasih. Apabila selama kegiatan terdapat '
        . 'kekurangan dalam pelayanan kami, kami memohon maaf sebesar-besarnya dan akan menjadikannya sebagai bahan evaluasi '
        . 'untuk menjadi lebih baik ke depannya.</p>'
        . '<p>Semoga pengalaman ini menjadi cerita yang menyenangkan untuk dikenang, dan kami berharap dapat kembali bertemu '
        . 'dalam petualangan berikutnya bersama Maua Project.</p>'
        . '<p>Sampai jumpa di perjalanan selanjutnya.</p>'
        . '<p>Salam hangat,<br><strong>Maua Project</strong></p>'
        . '</div>';
}

function buildReminderMessage(PDO $pdo, array $booking, string $type): array
{
    if ($type === 'H7') {
        $subjectTemplate = trim((string) ($booking['h7_reminder_subject'] ?? ''));
        $bodyTemplate = trim((string) ($booking['h7_reminder_body'] ?? ''));
        $subject = renderH7Template($subjectTemplate !== '' ? $subjectTemplate : defaultH7Subject(), $booking);
        $subject = trim(preg_replace('/[\r\n]+/', ' ', $subject) ?? $subject);
        $body = renderH7Template($bodyTemplate !== '' ? $bodyTemplate : defaultH7Body(), $booking);
        $content = '<div style="line-height:1.65">' . nl2br(reminderEscape($body)) . '</div>'
            . '<p>Ada pertanyaan? Hubungi admin: ' . adminContactHtml() . '</p>';
        return ['subject' => $subject, 'html' => reminderLayout('Pengingat H-7', $content), 'attachments' => []];
    }

    if ($type === 'H1') {
        $invoice = fetchReminderInvoice($pdo, $booking);
        return [
            'subject' => "Informasi Pelunasan – {$booking['trip_name']}",
            'html' => reminderLayout(
                'Informasi Pelunasan – ' . (string) $booking['trip_name'],
                h1ReminderHtml($booking, $invoice)
            ),
            'attachments' => [[
                'filename' => 'invoice-MAUA-' . $invoice['bookingId'] . '.pdf',
                'contentType' => 'application/pdf',
                'content' => createInvoicePdf($invoice),
            ]],
        ];
    }

    return [
        'subject' => 'Love Letter from Maua Project 💌',
        'html' => reminderLayout('Love Letter from Maua Project 💌', hPlus1ReminderHtml($booking)),
        'attachments' => [],
    ];
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
    $reminderWindows = [
        'H7' => [
            $today->modify('+2 days')->format('Y-m-d'),
            $today->modify('+7 days')->format('Y-m-d'),
        ],
        'H1' => [
            $today->modify('+1 day')->format('Y-m-d'),
            $today->modify('+1 day')->format('Y-m-d'),
        ],
        'HPLUS1' => [
            $today->modify('-1 day')->format('Y-m-d'),
            $today->modify('-1 day')->format('Y-m-d'),
        ],
    ];

    $pdo->exec(
        "UPDATE bookings
         SET archived_at=COALESCE(archived_at, NOW())
         WHERE DATE_ADD(
             TIMESTAMP(selected_date, COALESCE(end_time, '23:59:59')),
             INTERVAL 1 DAY
         ) < NOW()"
    );
    $pdo->exec(
        "UPDATE trip_schedules
         SET archived_at=COALESCE(archived_at, NOW()),
             status=CASE WHEN status='inactive' THEN status ELSE 'inactive' END
         WHERE DATE_ADD(
             TIMESTAMP(schedule_date, COALESCE(end_time, '23:59:59')),
             INTERVAL 1 DAY
         ) < NOW()"
    );

    $candidate = $pdo->prepare(
        "SELECT b.*, t.name trip_name, t.h7_reminder_subject, t.h7_reminder_body
         FROM bookings b
         INNER JOIN trips t ON t.id = b.trip_id
         WHERE b.status IN ('Disetujui','Selesai')
           AND b.selected_date BETWEEN ? AND ?
           AND b.customer_email <> ''"
    );
    $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'archived' => true, 'details' => []];

    foreach ($reminderWindows as $type => [$startDate, $endDate]) {
        $candidate->execute([$startDate, $endDate]);
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
