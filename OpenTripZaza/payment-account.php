<?php
declare(strict_types=1);

$databaseFile = __DIR__ . '/api/config/database.php';
if (!is_file($databaseFile)) {
    $databaseFile = dirname(__DIR__) . '/api/config/database.php';
}
require_once $databaseFile;
loadLocalEnvironment();
require_once dirname($databaseFile) . '/response.php';

requireMethod('GET');

$accountNumber = trim((string) getenv('VITE_BCA_ACCOUNT_NUMBER'));
$accountName = trim((string) getenv('VITE_BCA_ACCOUNT_NAME'));

if (!ctype_digit($accountNumber) || $accountName === '') {
    jsonError('Informasi rekening pembayaran sedang tidak tersedia.', 503);
}

jsonSuccess(['accountNumber' => $accountNumber, 'accountName' => $accountName]);
