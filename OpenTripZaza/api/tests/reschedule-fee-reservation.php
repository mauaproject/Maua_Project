<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once dirname(__DIR__) . '/reschedules/helper.php';

function expectSame(mixed $actual, mixed $expected, string $case): void
{
    if ($actual !== $expected) {
        throw new RuntimeException("{$case}: expected " . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

$today = appNow()->setTime(0, 0);
foreach ([8 => 0, 7 => 200000.0, 1 => 200000.0] as $days => $expected) {
    $fee = rescheduleFee([
        'selected_date' => $today->modify("+{$days} days")->format('Y-m-d'),
        'total_price' => 1000000,
    ]);
    expectSame($fee['amount'], $expected, "H-{$days} fee");
}
try {
    rescheduleFee(['selected_date' => $today->format('Y-m-d'), 'total_price' => 1000000]);
    throw new RuntimeException('Same-day reschedule should be rejected.');
} catch (InvalidArgumentException) {
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->sqliteCreateFunction('NOW', static fn(): string => date('Y-m-d H:i:s'));
$pdo->exec('CREATE TABLE bookings (id INTEGER PRIMARY KEY, schedule_id INTEGER, participants INTEGER, status TEXT)');
$pdo->exec('CREATE TABLE booking_reschedule_requests (booking_id INTEGER, requested_schedule_id INTEGER, status TEXT, payment_expires_at TEXT)');
$pdo->exec("INSERT INTO bookings VALUES (1, 10, 3, 'Disetujui'), (2, 20, 3, 'Disetujui')");

// A booking of three people cannot move into a target with only two places left.
expectSame(5 - getOpenTripReservedParticipants($pdo, 20), 2, 'Remaining target slots');

$future = $today->modify('+1 day')->format('Y-m-d H:i:s');
$pdo->prepare("INSERT INTO booking_reschedule_requests VALUES (1, 20, 'awaiting_payment', ?)")->execute([$future]);
expectSame(getOpenTripReservedParticipants($pdo, 20), 6, 'Active reschedule holds all three places');

$pdo->exec("UPDATE booking_reschedule_requests SET payment_expires_at = '2000-01-01 00:00:00'");
expectSame(getOpenTripReservedParticipants($pdo, 20), 3, 'Expired unpaid hold is excluded');

$pdo->exec("UPDATE booking_reschedule_requests SET status = 'pending'");
expectSame(getOpenTripReservedParticipants($pdo, 20), 6, 'Submitted proof keeps the hold');

echo "Reschedule fee and reservation checks passed.\n";
