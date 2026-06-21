<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $tripId = filter_input(INPUT_GET, 'trip_id', FILTER_VALIDATE_INT);
    if (!$tripId) {
        throw new InvalidArgumentException('ID trip tidak valid.');
    }
    $statement = $pdo->prepare(
        "SELECT id, trip_id, session_id, selected_date, status
         FROM bookings
         WHERE trip_id=? AND trip_type='private'
           AND status IN ('Menunggu Approval','Disetujui')"
    );
    $statement->execute([$tripId]);
    $sessionCodes = [];
    $sessions = $pdo->prepare('SELECT id, session_code FROM trip_sessions WHERE trip_id=?');
    $sessions->execute([$tripId]);
    foreach ($sessions->fetchAll() as $session) {
        $sessionCodes[(int) $session['id']] = $session['session_code'] ?: (string) $session['id'];
    }
    jsonSuccess(array_map(static fn(array $booking): array => [
        'id' => (int) $booking['id'],
        'tripId' => (int) $booking['trip_id'],
        'tripType' => 'private',
        'isPrivateTrip' => true,
        'sessionId' => $sessionCodes[(int) $booking['session_id']] ?? '',
        'selectedDate' => $booking['selected_date'],
        'requestedDate' => $booking['selected_date'],
        'status' => $booking['status'],
    ], $statement->fetchAll()));
});
