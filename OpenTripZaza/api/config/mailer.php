<?php
declare(strict_types=1);

function mailEnvironment(): array
{
    return [
        'host' => trim((string) getenv('MAIL_HOST')),
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'encryption' => strtolower(trim((string) (getenv('MAIL_ENCRYPTION') ?: 'tls'))),
        'username' => trim((string) getenv('MAIL_USERNAME')),
        'password' => (string) getenv('MAIL_PASSWORD'),
        'fromAddress' => trim((string) getenv('MAIL_FROM_ADDRESS')),
        'fromName' => trim((string) (getenv('MAIL_FROM_NAME') ?: 'MAUA Project')),
    ];
}

function smtpReadResponse($stream): array
{
    $lines = [];
    while (($line = fgets($stream, 515)) !== false) {
        $lines[] = rtrim($line, "\r\n");
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }
    if (!$lines) {
        throw new RuntimeException('SMTP tidak memberikan respons.');
    }
    return [(int) substr($lines[count($lines) - 1], 0, 3), implode("\n", $lines)];
}

function smtpCommand($stream, string $command, array $expectedCodes): string
{
    if ($command !== '') {
        $written = fwrite($stream, $command . "\r\n");
        if ($written === false) {
            throw new RuntimeException('Gagal menulis perintah ke SMTP.');
        }
    }
    [$code, $response] = smtpReadResponse($stream);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException("SMTP menolak perintah ({$code}): {$response}");
    }
    return $response;
}

function encodedMailHeader(string $value): string
{
    if (function_exists('mb_encode_mimeheader')) {
        return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
    }
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function buildMimeMessage(
    array $config,
    string $to,
    string $subject,
    string $html,
    array $attachments = []
): string {
    $boundary = 'mixed_' . bin2hex(random_bytes(12));
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . encodedMailHeader($config['fromName']) . ' <' . $config['fromAddress'] . '>',
        'To: <' . $to . '>',
        'Subject: ' . encodedMailHeader($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . preg_replace('/^.*@/', '', $config['fromAddress']) . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
    ];
    $parts = [
        '--' . $boundary,
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: quoted-printable',
        '',
        quoted_printable_encode($html),
    ];
    foreach ($attachments as $attachment) {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($attachment['filename'] ?? 'attachment.bin'));
        $parts[] = '--' . $boundary;
        $parts[] = 'Content-Type: ' . ($attachment['contentType'] ?? 'application/octet-stream') . '; name="' . $filename . '"';
        $parts[] = 'Content-Transfer-Encoding: base64';
        $parts[] = 'Content-Disposition: attachment; filename="' . $filename . '"';
        $parts[] = '';
        $parts[] = rtrim(chunk_split(base64_encode((string) ($attachment['content'] ?? '')), 76, "\r\n"));
    }
    $parts[] = '--' . $boundary . '--';
    $parts[] = '';
    return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $parts);
}

function sendSmtpMail(
    string $to,
    string $subject,
    string $html,
    array $attachments = []
): void {
    $config = mailEnvironment();
    foreach (['host', 'username', 'password', 'fromAddress'] as $required) {
        if ($config[$required] === '') {
            throw new RuntimeException("Konfigurasi {$required} untuk SMTP belum diisi.");
        }
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($config['fromAddress'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Alamat email pengirim atau penerima tidak valid.');
    }

    $transport = $config['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
    $stream = @stream_socket_client(
        $transport . $config['host'] . ':' . $config['port'],
        $errorNumber,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT
    );
    if (!is_resource($stream)) {
        throw new RuntimeException("Tidak dapat terhubung ke SMTP: {$errorMessage} ({$errorNumber}).");
    }
    stream_set_timeout($stream, 20);

    try {
        smtpCommand($stream, '', [220]);
        $hostname = gethostname() ?: 'localhost';
        smtpCommand($stream, 'EHLO ' . $hostname, [250]);

        if ($config['encryption'] === 'tls') {
            smtpCommand($stream, 'STARTTLS', [220]);
            $cryptoEnabled = stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($cryptoEnabled !== true) {
                throw new RuntimeException('Gagal mengaktifkan enkripsi TLS SMTP.');
            }
            smtpCommand($stream, 'EHLO ' . $hostname, [250]);
        }

        smtpCommand($stream, 'AUTH LOGIN', [334]);
        smtpCommand($stream, base64_encode($config['username']), [334]);
        smtpCommand($stream, base64_encode($config['password']), [235]);
        smtpCommand($stream, 'MAIL FROM:<' . $config['fromAddress'] . '>', [250]);
        smtpCommand($stream, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtpCommand($stream, 'DATA', [354]);

        $message = buildMimeMessage($config, $to, $subject, $html, $attachments);
        $message = preg_replace('/(?m)^\./', '..', $message);
        if (fwrite($stream, $message . "\r\n.\r\n") === false) {
            throw new RuntimeException('Gagal mengirim isi email ke SMTP.');
        }
        smtpCommand($stream, '', [250]);
        smtpCommand($stream, 'QUIT', [221]);
    } finally {
        fclose($stream);
    }
}
