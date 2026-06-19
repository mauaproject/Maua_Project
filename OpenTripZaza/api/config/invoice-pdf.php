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

function pdfTextWidth(string $text, float $fontSize): float
{
    return strlen(pdfSafeText($text)) * $fontSize * 0.52;
}

function pdfText(
    string $text,
    float $x,
    float $y,
    float $fontSize = 10,
    bool $bold = false,
    string $align = 'left'
): string {
    if ($align === 'right') {
        $x -= pdfTextWidth($text, $fontSize);
    } elseif ($align === 'center') {
        $x -= pdfTextWidth($text, $fontSize) / 2;
    }
    return sprintf(
        "BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
        $bold ? 'F2' : 'F1',
        $fontSize,
        $x,
        $y,
        pdfSafeText($text)
    );
}

function pdfLine(float $x1, float $y1, float $x2, float $y2, float $width = 0.7): string
{
    return sprintf("%.2F w %.2F %.2F m %.2F %.2F l S", $width, $x1, $y1, $x2, $y2);
}

function pdfWrappedText(
    string $text,
    float $x,
    float $y,
    int $characters = 42,
    float $fontSize = 9,
    bool $bold = false,
    float $lineHeight = 13
): array {
    $commands = [];
    foreach (explode("\n", wordwrap($text, $characters, "\n", true)) as $line) {
        if ($line !== '') {
            $commands[] = pdfText($line, $x, $y, $fontSize, $bold);
        }
        $y -= $lineHeight;
    }
    return [$commands, $y];
}

function invoiceLogo(): ?array
{
    $path = dirname(__DIR__) . '/assets/invoice-logo.jpg';
    if (!is_file($path) || !is_readable($path)) {
        return null;
    }
    $bytes = file_get_contents($path);
    $size = getimagesize($path);
    if ($bytes === false || $size === false || ($size['mime'] ?? '') !== 'image/jpeg') {
        return null;
    }
    return ['bytes' => $bytes, 'width' => (int) $size[0], 'height' => (int) $size[1]];
}

function createInvoicePdf(array $invoice): string
{
    $commands = [
        '0.980 0.976 0.965 rg 0 0 595 842 re f',
        '0.15 0.15 0.14 RG',
        '0.15 0.15 0.14 rg',
        pdfLine(48, 790, 547, 790, 0.8),
        pdfText('I N V O I C E', 52, 753, 22),
        pdfLine(48, 730, 547, 730, 0.8),
    ];

    $logo = invoiceLogo();
    if ($logo) {
        $commands[] = 'q 155 0 0 46 390 741 cm /Im1 Do Q';
    } else {
        $commands[] = pdfText('MAUA PROJECT', 542, 755, 11, true, 'right');
    }

    $commands[] = pdfText('INVOICE TO', 52, 674, 9);
    $commands[] = pdfText(strtoupper((string) $invoice['customerName']), 52, 652, 10);
    $commands[] = pdfText((string) $invoice['customerEmail'], 52, 635, 9);
    $commands[] = pdfText('INVOICE DATE : ' . $invoice['invoiceDate'], 542, 674, 9, false, 'right');
    $commands[] = pdfText('TRIP DATE : ' . $invoice['tripDate'], 542, 648, 9, false, 'right');

    $commands[] = pdfLine(54, 594, 541, 594, 0.8);
    $commands[] = pdfText('DESCRIPTION', 110, 570, 9, false, 'center');
    $commands[] = pdfText('PRICE', 350, 570, 9, false, 'center');
    $commands[] = pdfText('SUBTOTAL', 490, 570, 9, false, 'center');
    $commands[] = pdfLine(54, 553, 541, 553, 0.8);

    $rowY = 525.0;
    $tripDescription = $invoice['participants'] . ' pax ' . $invoice['tripName'];
    [$descriptionCommands] = pdfWrappedText($tripDescription, 70, $rowY, 38, 9, false, 12);
    $commands = array_merge($commands, $descriptionCommands);
    $commands[] = pdfText(pdfMoney((float) $invoice['pricePerPerson']), 350, $rowY, 9, false, 'center');
    $commands[] = pdfText(pdfMoney((float) $invoice['tripSubtotal']), 490, $rowY, 9, false, 'center');
    $rowY -= 42;

    foreach ($invoice['addons'] as $addon) {
        [$addonCommands] = pdfWrappedText('Add-on ' . $addon['name'], 70, $rowY, 38, 9, false, 12);
        $commands = array_merge($commands, $addonCommands);
        $commands[] = pdfText(pdfMoney((float) $addon['price']), 350, $rowY, 9, false, 'center');
        $commands[] = pdfText(pdfMoney((float) $addon['total']), 490, $rowY, 9, false, 'center');
        $rowY -= 38;
    }

    $dividerY = min(300.0, $rowY - 22);
    $dividerY = max(190.0, $dividerY);
    $commands[] = pdfLine(54, $dividerY, 541, $dividerY, 0.8);

    $paymentY = $dividerY - 70;
    $commands[] = pdfText('Payment details :', 72, $paymentY, 9);
    $paymentY -= 22;
    foreach ($invoice['paymentLines'] as $paymentLine) {
        [$paymentCommands, $paymentY] = pdfWrappedText((string) $paymentLine, 72, $paymentY, 44, 8.5, false, 12);
        $commands = array_merge($commands, $paymentCommands);
    }
    if ($invoice['paymentInstructions'] !== '') {
        [$instructionCommands] = pdfWrappedText(
            (string) $invoice['paymentInstructions'],
            72,
            $paymentY - 3,
            44,
            8.5,
            true,
            12
        );
        $commands = array_merge($commands, $instructionCommands);
    }

    $summaryY = $dividerY - 34;
    $commands[] = pdfText('Sub-total :', 430, $summaryY, 9, false, 'right');
    $commands[] = pdfText(pdfMoney((float) $invoice['subtotal']), 525, $summaryY, 9, false, 'right');
    $summaryY -= 28;
    $commands[] = pdfText('DP / Dibayar :', 430, $summaryY, 9, false, 'right');
    $commands[] = pdfText(pdfMoney((float) $invoice['paidAmount']), 525, $summaryY, 9, false, 'right');
    $summaryY -= 28;
    $commands[] = pdfText('Sisa pembayaran :', 430, $summaryY, 10, true, 'right');
    $commands[] = pdfText(pdfMoney((float) $invoice['balanceDue']), 525, $summaryY, 10, true, 'right');

    $instagram = trim((string) (getenv('INSTAGRAM_HANDLE') ?: '@mauaproject'));
    $whatsapp = trim((string) getenv('ADMIN_WHATSAPP'));
    $footer = $instagram . ($whatsapp !== '' ? ' | ' . $whatsapp : '');
    $commands[] = pdfText($footer, 297.5, 58, 8.5, false, 'center');

    $content = implode("\n", $commands);
    $imageObjectNumber = $logo ? 6 : null;
    $contentObjectNumber = $logo ? 7 : 6;
    $resources = '/Font << /F1 4 0 R /F2 5 0 R >>';
    if ($logo) {
        $resources .= ' /XObject << /Im1 ' . $imageObjectNumber . ' 0 R >>';
    }

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << '
            . $resources . ' >> /Contents ' . $contentObjectNumber . ' 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
    ];
    if ($logo) {
        $objects[] = '<< /Type /XObject /Subtype /Image /Width ' . $logo['width']
            . ' /Height ' . $logo['height']
            . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '
            . strlen($logo['bytes']) . " >>\nstream\n" . $logo['bytes'] . "\nendstream";
    }
    $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $number = $index + 1;
        $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
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
