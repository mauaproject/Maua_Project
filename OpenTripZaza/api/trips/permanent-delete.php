<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'confirmation', 'adminEmail']);
    $tripId = (int) $data['id'];
    if ((string) $data['confirmation'] !== 'HAPUS PERMANEN') {
        throw new InvalidArgumentException('Ketik HAPUS PERMANEN untuk mengonfirmasi penghapusan.');
    }
    $adminEmail = strtolower(trim((string) $data['adminEmail']));
    $adminStatement = $pdo->prepare(
        "SELECT id FROM users WHERE email=? AND role='admin' LIMIT 1"
    );
    $adminStatement->execute([$adminEmail]);
    if (!$adminStatement->fetch()) {
        jsonError('Akses admin diperlukan untuk menghapus trip secara permanen.', 403);
    }

    $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE id=?');
    $tripStatement->execute([$tripId]);
    $trip = $tripStatement->fetch();
    if (!$trip) {
        jsonError('Trip tidak ditemukan.', 404);
    }

    $scheduleStatement = $pdo->prepare(
        'SELECT schedule_date, end_time FROM trip_schedules WHERE trip_id=? ORDER BY schedule_date, end_time'
    );
    $scheduleStatement->execute([$tripId]);
    $schedules = $scheduleStatement->fetchAll();
    $sessionStatement = $pdo->prepare(
        'SELECT end_time FROM trip_sessions WHERE trip_id=? ORDER BY end_time'
    );
    $sessionStatement->execute([$tripId]);
    $sessions = $sessionStatement->fetchAll();
    $lastEndAt = tripLastEndAt($trip, $schedules, $sessions);
    $deleteEligibleAt = $lastEndAt?->modify('+37 days');
    if (
        tripLifecycleStatus($trip, $schedules, $sessions) !== 'archived'
        || $deleteEligibleAt === null
        || $deleteEligibleAt > appNow()
    ) {
        $availableAt = $deleteEligibleAt?->format('d-m-Y H:i') ?? '-';
        throw new InvalidArgumentException(
            "Trip belum melewati masa retensi arsip 30 hari. Hapus permanen tersedia pada {$availableAt} WIB."
        );
    }

    $tripImageStatement = $pdo->prepare(
        'SELECT image_url, thumbnail_url FROM trip_images WHERE trip_id=?'
    );
    $tripImageStatement->execute([$tripId]);
    $tripImages = $tripImageStatement->fetchAll();
    $paymentFileStatement = $pdo->prepare(
        'SELECT p.payment_proof_url
         FROM payments p INNER JOIN bookings b ON b.id=p.booking_id
         WHERE b.trip_id=?'
    );
    $paymentFileStatement->execute([$tripId]);
    $paymentFiles = $paymentFileStatement->fetchAll();
    $workerFileStatement = $pdo->prepare(
        'SELECT proof_photo_url FROM worker_tasks WHERE trip_id=?'
    );
    $workerFileStatement->execute([$tripId]);
    $workerFiles = $workerFileStatement->fetchAll();

    $pdo->beginTransaction();
    try {
        $bookingSubquery = 'SELECT id FROM bookings WHERE trip_id=?';
        $pdo->prepare("DELETE FROM reminder_logs WHERE booking_id IN ({$bookingSubquery})")->execute([$tripId]);
        $pdo->prepare('DELETE FROM reviews WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM worker_tasks WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare("DELETE FROM payments WHERE booking_id IN ({$bookingSubquery})")->execute([$tripId]);
        $pdo->prepare("DELETE FROM booking_addons WHERE booking_id IN ({$bookingSubquery})")->execute([$tripId]);
        $pdo->prepare("DELETE FROM booking_participants WHERE booking_id IN ({$bookingSubquery})")->execute([$tripId]);
        $pdo->prepare('DELETE FROM bookings WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare(
            'DELETE FROM package_price_tiers
             WHERE package_id IN (SELECT id FROM private_trip_packages WHERE trip_id=?)'
        )->execute([$tripId]);
        $pdo->prepare('DELETE FROM private_trip_packages WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM private_price_tiers WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM trip_addons WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM trip_images WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM trip_sessions WHERE trip_id=?')->execute([$tripId]);
        $pdo->prepare('DELETE FROM trip_schedules WHERE trip_id=?')->execute([$tripId]);
        $deleteTrip = $pdo->prepare('DELETE FROM trips WHERE id=?');
        $deleteTrip->execute([$tripId]);
        if ($deleteTrip->rowCount() !== 1) {
            throw new RuntimeException('Trip gagal dihapus.');
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    foreach ($tripImages as $image) {
        deleteStoredUpload((string) ($image['image_url'] ?? ''), 'trips');
        deleteStoredUpload((string) ($image['thumbnail_url'] ?? ''), 'trips');
    }
    foreach ($paymentFiles as $payment) {
        deleteStoredUpload((string) ($payment['payment_proof_url'] ?? ''), 'payment-proofs');
    }
    foreach ($workerFiles as $workerFile) {
        deleteStoredUpload((string) ($workerFile['proof_photo_url'] ?? ''), 'worker-proofs');
    }

    jsonSuccess([
        'id' => $tripId,
        'deleted' => true,
        'message' => 'Trip arsip dan seluruh data terkait telah dihapus permanen.',
    ]);
});
