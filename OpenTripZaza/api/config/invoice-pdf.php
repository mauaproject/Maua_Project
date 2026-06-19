<?php
declare(strict_types=1);

function pdfSafeText(string $value): string
{
    $converted = function_exists('iconv')
        ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value)
        : $value;
    $converted = $converted === false ? $value : $converted;
    return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $converted);
}

function pdfMoney(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function wrapPdfLine(string $text, int $width = 86): array
{
    $lines = explode("\n", wordwrap($text, $width, "\n", true));
    return array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
}

function createInvoicePdf(array $invoice): string
{
    $lines = [
        ['MAUA PROJECT', 18, true],
        ['INVOICE #' . $invoice['bookingId'], 14, true],
        ['Invoice date: ' . $invoice['invoiceDate'], 10, false],
        ['Trip date: ' . $invoice['tripDate'], 10, false],
        ['', 6, false],
        ['INVOICE TO', 11, true],
        [$invoice['customerName'], 10, false],
        [$invoice['customerEmail'], 10, false],
        ['', 6, false],
        ['DETAIL ITEM', 11, true],
        [
            $invoice['tripName'] . ' - ' . $invoice['participants'] . ' peserta x ' .
            pdfMoney($invoice['pricePerPerson']) . ' = ' . pdfMoney($invoice['tripSubtotal']),
            10,
            false,
        ],
    ];
    foreach ($invoice['addons'] as $addon) {
        $lines[] = ['Add-on: ' . $addon['name'] . ' = ' . pdfMoney($addon['total']), 10, false];
    }
    $lines = array_merge($lines, [
        ['', 6, false],
        ['Subtotal: ' . pdfMoney($invoice['subtotal']), 10, true],
        ['DP / pembayaran tercatat: ' . pdfMoney($invoice['paidAmount']), 10, false],
        ['Sisa pembayaran: ' . pdfMoney($invoice['balanceDue']), 11, true],
        ['', 6, false],
        ['PAYMENT DETAILS', 11, true],
    ]);
    foreach ($invoice['paymentLines'] as $paymentLine) {
        $lines[] = [$paymentLine, 9, false];
    }
    if ($invoice['paymentInstructions'] !== '') {
        foreach (wrapPdfLine($invoice['paymentInstructions']) as $instructionLine) {
            $lines[] = [$instructionLine, 9, false];
        }
    }

    $commands = ["BT", "50 790 Td"];
    foreach ($lines as [$text, $fontSize, $bold]) {
        $commands[] = '/' . ($bold ? 'F2' : 'F1') . ' ' . $fontSize . ' Tf';
        if ($text !== '') {
            foreach (wrapPdfLine((string) $text) as $index => $wrappedLine) {
                if ($index > 0) {
                    $commands[] = '0 -14 Td';
                }
                $commands[] = '(' . pdfSafeText($wrappedLine) . ') Tj';
            }
        }
        $commands[] = '0 -' . max(12, $fontSize + 4) . ' Td';
    }
    $commands[] = 'ET';
    $content = implode("\n", $commands);

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream",
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $objectNumber = $index + 1;
        $pdf .= "{$objectNumber} 0 obj\n{$object}\nendobj\n";
    }
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach (array_slice($offsets, 1) as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xrefOffset}\n%%EOF";
    return $pdf;
}
