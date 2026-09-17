<?php
declare(strict_types=1);
require_once __DIR__ . '/helper.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $user = requireRescheduleUser($pdo, 'customer');
    expireUnpaidReschedules($pdo);
    $requestId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$requestId || !isset($_FILES['proof'])) {
        throw new InvalidArgumentException('Pengajuan dan bukti transfer wajib diisi.');
    }

    $storedProof = null;
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "SELECT r.*, b.user_id, b.status booking_status, b.selected_date booking_date,
                    b.schedule_id booking_schedule_id, b.session_id booking_session_id
             FROM booking_reschedule_requests r
             INNER JOIN bookings b ON b.id = r.booking_id
             WHERE r.id = ? AND b.user_id = ? FOR UPDATE"
        );
        $statement->execute([$requestId, (int) $user['id']]);
        $request = $statement->fetch();
        if (!$request) {
            throw new InvalidArgumentException('Pengajuan reschedule tidak ditemukan.');
        }
        if ($request['status'] !== 'awaiting_payment'
            || $request['payment_expires_at'] <= appNow()->format('Y-m-d H:i:s')) {
            throw new InvalidArgumentException('Waktu pembayaran reschedule telah habis atau pengajuan sudah diproses.');
        }
        if ((float) $request['fee_amount'] <= 0 || $request['payment_proof_url'] !== null
            || $request['booking_status'] !== 'Disetujui'
            || $request['old_selected_date'] !== $request['booking_date']
            || nullableInt($request['old_schedule_id']) !== nullableInt($request['booking_schedule_id'])
            || nullableInt($request['old_session_id']) !== nullableInt($request['booking_session_id'])) {
            throw new InvalidArgumentException('Pengajuan reschedule sudah tidak sesuai dengan booking.');
        }

        $storedProof = storeUploadedImage($_FILES['proof'], 'payment-proofs');
        $pdo->prepare(
            "UPDATE booking_reschedule_requests
             SET payment_proof_url = ?, payment_submitted_at = NOW(), status = 'pending'
             WHERE id = ?"
        )->execute([$storedProof['path'], $requestId]);
        $pdo->commit();

        $result = $pdo->prepare(rescheduleSelectSql() . ' WHERE r.id = ?');
        $result->execute([$requestId]);
        jsonSuccess(mapRescheduleRows($result->fetchAll())[0]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (is_array($storedProof) && !empty($storedProof['path'])) {
            deleteStoredUpload((string) $storedProof['path'], 'payment-proofs');
        }
        throw $exception;
    }
});
