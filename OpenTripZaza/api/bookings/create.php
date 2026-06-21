<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $isMultipart = isset($_POST['booking_data']);
    if ($isMultipart) {
        $data = json_decode((string) $_POST['booking_data'], true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data checkout tidak valid.');
        }
    } else {
        $data = jsonInput();
    }
    requiredFields($data, ['userId', 'tripId', 'name', 'email', 'whatsapp', 'participants']);
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email customer tidak valid.');
    }
    $paymentType = trim((string) ($data['paymentType'] ?? ''));
    $hasPayment = $paymentType !== '' || isset($_FILES['proof']);
    if ($hasPayment && !in_array($paymentType, ['dp', 'full'], true)) {
        throw new InvalidArgumentException('Pilihan pembayaran harus DP atau Lunas.');
    }
    if ($hasPayment && !isset($_FILES['proof'])) {
        throw new InvalidArgumentException('Bukti pembayaran wajib diunggah.');
    }
    $storedPaymentProof = null;
    $pdo->beginTransaction();
    try {
        $primary = is_array($data['participantDetails'] ?? null) ? ($data['participantDetails'][0] ?? []) : [];
        $email = strtolower(trim((string) $data['email']));
        $userLookup = $pdo->prepare(
            "SELECT * FROM users WHERE id=? AND email=? AND role='customer' FOR UPDATE"
        );
        $userLookup->execute([(int) $data['userId'], $email]);
        $user = $userLookup->fetch();
        if (!$user) {
            jsonError('Akun customer tidak ditemukan. Silakan login kembali.', 401);
        }
        if (!(bool) $user['email_verified']) {
            jsonError('Verifikasi email terlebih dahulu sebelum melanjutkan pendaftaran.', 403);
        }
        customerTripProfileValues([
            'bloodType' => $user['blood_type'] ?? null,
            'heightCm' => $user['height_cm'] ?? null,
            'weightKg' => $user['weight_kg'] ?? null,
            'shoeSize' => $user['shoe_size'] ?? null,
        ], true);
        $userId = (int) $user['id'];
        $userValues = [
            $data['name'], $data['whatsapp'],
            $primary['address'] ?? $data['address'] ?? null,
            nullableInt($primary['age'] ?? $data['age'] ?? null),
            $primary['gender'] ?? $data['gender'] ?? null,
            $primary['healthNotes'] ?? $data['healthNotes'] ?? null,
        ];
        $userStatement = $pdo->prepare(
            'UPDATE users SET name=?, whatsapp=?, address=?, age=?, gender=?, health_notes=?, updated_at=CURRENT_TIMESTAMP WHERE id=?'
        );
        $userStatement->execute([...$userValues, $userId]);

        $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE id = ? FOR UPDATE');
        $tripStatement->execute([(int) $data['tripId']]);
        $trip = $tripStatement->fetch();
        if (!$trip || !in_array($trip['status'], ['Tersedia', 'Penuh'], true)) {
            throw new InvalidArgumentException('Trip tidak tersedia.');
        }
        $tripType = $trip['trip_type'] === 'private' ? 'private' : 'open';

        $participants = max(1, (int) $data['participants']);
        $pricePerPerson = (float) $trip['price'];
        $selectedPackage = null;
        if ($tripType === 'private') {
            $selectedPackageId = nullableInt($data['selectedPackageId'] ?? null);
            $activePackageStatement = $pdo->prepare(
                "SELECT COUNT(*) FROM private_trip_packages WHERE trip_id=? AND status='active'"
            );
            $activePackageStatement->execute([(int) $trip['id']]);
            $hasActivePackages = (int) $activePackageStatement->fetchColumn() > 0;
            if ($hasActivePackages) {
                if (!$selectedPackageId) {
                    throw new InvalidArgumentException('Pilih salah satu paket private trip.');
                }
                $packageStatement = $pdo->prepare(
                    "SELECT id, name, price, max_custom_pax, destinations_json
                     FROM private_trip_packages
                     WHERE id=? AND trip_id=? AND status='active' FOR UPDATE"
                );
                $packageStatement->execute([$selectedPackageId, (int) $trip['id']]);
                $selectedPackage = $packageStatement->fetch();
                if (!$selectedPackage) {
                    throw new InvalidArgumentException('Paket private trip tidak tersedia.');
                }
                $appliedPax = min($participants, max(1, (int) $selectedPackage['max_custom_pax']));
                $tierStatement = $pdo->prepare(
                    'SELECT price_per_person FROM package_price_tiers
                     WHERE package_id=? AND pax_count<=? ORDER BY pax_count DESC LIMIT 1'
                );
                $tierStatement->execute([(int) $selectedPackage['id'], $appliedPax]);
                $tierPrice = $tierStatement->fetchColumn();
                $pricePerPerson = $tierPrice !== false ? (float) $tierPrice : (float) $selectedPackage['price'];
            } else {
                $appliedPax = min($participants, max(1, (int) ($trip['max_custom_pax'] ?? $participants)));
                $tierStatement = $pdo->prepare(
                    'SELECT price_per_person FROM private_price_tiers
                     WHERE trip_id=? AND pax_count<=? ORDER BY pax_count DESC LIMIT 1'
                );
                $tierStatement->execute([(int) $trip['id'], $appliedPax]);
                $tierPrice = $tierStatement->fetchColumn();
                if ($tierPrice !== false) {
                    $pricePerPerson = (float) $tierPrice;
                }
            }
        }

        $requestedAddonIds = array_values(array_unique(array_filter(array_map(
            static fn(mixed $value): int => (int) $value,
            (array) ($data['addons'] ?? [])
        ))));
        $selectedAddons = [];
        if ($requestedAddonIds) {
            $placeholders = implode(',', array_fill(0, count($requestedAddonIds), '?'));
            $addonLookup = $pdo->prepare(
                "SELECT id, name, price, worker_action FROM trip_addons
                 WHERE trip_id = ? AND status = 'active' AND id IN ($placeholders)"
            );
            $addonLookup->execute([(int) $trip['id'], ...$requestedAddonIds]);
            $selectedAddons = $addonLookup->fetchAll();
            if (count($selectedAddons) !== count($requestedAddonIds)) {
                throw new InvalidArgumentException('Salah satu add-on tidak tersedia untuk trip ini.');
            }
        }
        $addonTotal = array_sum(array_map(static fn(array $addon): float => (float) $addon['price'], $selectedAddons));
        $tripSubtotal = $participants * $pricePerPerson;
        $packagePrice = $selectedPackage ? $pricePerPerson : 0;
        $totalPrice = $tripSubtotal + $addonTotal;
        $requiredPaymentAmount = $paymentType === 'full'
            ? $totalPrice
            : round($totalPrice * 0.5);
        $paymentStatus = $hasPayment ? 'waiting_verification' : null;
        $bcaAccountNumber = trim((string) (getenv('VITE_BCA_ACCOUNT_NUMBER') ?: ($data['bcaAccountNumber'] ?? '')));

        $scheduleId = null;
        $selectedDate = $data['selectedDate'] ?? null;
        $bookingStartTime = $data['startTime'] ?? null;
        $bookingEndTime = $data['endTime'] ?? null;
        if ($tripType === 'open') {
            $scheduleStatement = $pdo->prepare('SELECT * FROM trip_schedules WHERE trip_id = ? AND (schedule_code = ? OR id = ?) FOR UPDATE');
            $scheduleStatement->execute([(int) $data['tripId'], $data['scheduleId'] ?? '', nullableInt($data['scheduleId'] ?? null)]);
            $schedule = $scheduleStatement->fetch();
            if (!$schedule || $schedule['status'] !== 'active') {
                throw new InvalidArgumentException('Jadwal trip tidak tersedia.');
            }
            if (((int) $schedule['quota'] - (int) $schedule['booked_count']) < (int) $data['participants']) {
                throw new InvalidArgumentException('Slot jadwal tidak mencukupi.');
            }
            $scheduleId = (int) $schedule['id'];
            $selectedDate = $schedule['schedule_date'];
            $bookingStartTime = $schedule['start_time'] ?? null;
            $bookingEndTime = $schedule['end_time'] ?? null;
        }

        $sessionId = null;
        if ($tripType === 'private') {
            $sessionStatement = $pdo->prepare('SELECT id, start_time, end_time FROM trip_sessions WHERE trip_id = ? AND (session_code = ? OR id = ?) AND status = "active"');
            $sessionStatement->execute([(int) $data['tripId'], $data['sessionId'] ?? '', nullableInt($data['sessionId'] ?? null)]);
            $selectedSession = $sessionStatement->fetch();
            if (!$selectedSession) {
                throw new InvalidArgumentException('Sesi private trip tidak tersedia.');
            }
            $sessionId = (int) $selectedSession['id'];
            $bookingStartTime = $selectedSession['start_time'] ?? null;
            $bookingEndTime = $selectedSession['end_time'] ?? null;
            if (($trip['private_booking_mode'] ?? 'exclusive') !== 'shared') {
                $collision = $pdo->prepare(
                    "SELECT COUNT(*) FROM bookings WHERE trip_id = ? AND session_id = ? AND selected_date = ?
                     AND status IN ('Menunggu Approval','Disetujui')"
                );
                $collision->execute([(int) $data['tripId'], $sessionId, $data['selectedDate'] ?? null]);
                if ((int) $collision->fetchColumn() > 0) {
                    throw new InvalidArgumentException('Sesi pada tanggal tersebut sudah dipesan.');
                }
            }
        }

        $statement = $pdo->prepare(
            'INSERT INTO bookings
            (user_id, trip_id, schedule_id, session_id, selected_package_id, selected_package_name,
             selected_package_price, selected_package_subtotal, selected_package_destinations, customer_name, customer_email, customer_whatsapp,
             trip_type, experience_type, selected_date, visible_until, start_time, end_time, participants, price_per_person,
             total_price, payment_type, payment_status, required_payment_amount, paid_amount, bca_account_number,
             status, notes, transport_from)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,DATE_ADD(?, INTERVAL 7 DAY),?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $statement->execute([
            $userId, (int) $data['tripId'], $scheduleId, $sessionId,
            $selectedPackage ? (int) $selectedPackage['id'] : null,
            $selectedPackage['name'] ?? null,
            $packagePrice,
            $selectedPackage ? $tripSubtotal : 0,
            $selectedPackage['destinations_json'] ?? null,
            $data['name'], strtolower($data['email']),
            $data['whatsapp'], $tripType, $trip['experience_type'] ?? 'cave',
            $selectedDate, $selectedDate,
            $bookingStartTime, $bookingEndTime,
            $participants, $pricePerPerson, $totalPrice,
            $hasPayment ? $paymentType : null, $paymentStatus,
            $hasPayment ? $requiredPaymentAmount : 0, $hasPayment ? $requiredPaymentAmount : 0,
            $hasPayment ? $bcaAccountNumber : null, 'Menunggu Approval',
            $data['notes'] ?? null, $data['transportFrom'] ?? null,
        ]);
        $bookingId = (int) $pdo->lastInsertId();

        $participantStatement = $pdo->prepare(
            'INSERT INTO booking_participants (booking_id, name, address, age, gender, health_notes) VALUES (?,?,?,?,?,?)'
        );
        foreach (($data['participantDetails'] ?? []) as $participant) {
            $participantStatement->execute([
                $bookingId, $participant['name'] ?? $data['name'], $participant['address'] ?? null,
                nullableInt($participant['age'] ?? null), $participant['gender'] ?? null, $participant['healthNotes'] ?? null,
            ]);
        }

        $addonStatement = $pdo->prepare(
            'INSERT INTO booking_addons (booking_id, addon_id, trip_addon_id, quantity, price) VALUES (?,NULL,?,1,?)'
        );
        foreach ($selectedAddons as $addon) {
            $addonStatement->execute([$bookingId, (int) $addon['id'], (float) $addon['price']]);
        }

        if ($hasPayment) {
            $storedPaymentProof = storeUploadedImage($_FILES['proof'], 'payment-proofs');
            $paymentStatement = $pdo->prepare(
                "INSERT INTO payments
                 (booking_id, amount, payment_method, payment_proof_url, payment_status, submitted_at)
                 VALUES (?,?,?,?,?,NOW())"
            );
            $paymentStatement->execute([
                $bookingId,
                $requiredPaymentAmount,
                trim((string) ($data['paymentChannel'] ?? 'qris_or_bca')),
                $storedPaymentProof['path'],
                $paymentStatus,
            ]);
        }

        $pdo->commit();
        $bookingStatement = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $bookingStatement->execute([$bookingId]);
        jsonSuccess(mapBooking($pdo, $bookingStatement->fetch()), 201);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (is_array($storedPaymentProof) && !empty($storedPaymentProof['path'])) {
            deleteStoredUpload((string) $storedPaymentProof['path'], 'payment-proofs');
        }
        throw $exception;
    }
});
