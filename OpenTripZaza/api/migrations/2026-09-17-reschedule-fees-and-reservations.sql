-- Jalankan setelah 2026-07-21-booking-reschedules.sql.
-- Status awaiting_payment menahan slot selama satu jam. Bukti dan biaya
-- reschedule disimpan pada pengajuan, terpisah dari pembayaran booking.
ALTER TABLE booking_reschedule_requests
  MODIFY status enum('awaiting_payment','pending','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
  ADD COLUMN fee_base_amount decimal(12,2) NOT NULL DEFAULT 0 AFTER reason,
  ADD COLUMN fee_amount decimal(12,2) NOT NULL DEFAULT 0 AFTER fee_base_amount,
  ADD COLUMN payment_proof_url varchar(500) DEFAULT NULL AFTER fee_amount,
  ADD COLUMN payment_submitted_at datetime DEFAULT NULL AFTER payment_proof_url,
  ADD COLUMN payment_expires_at datetime DEFAULT NULL AFTER payment_submitted_at,
  ADD KEY idx_reschedule_payment_expiry (status, payment_expires_at);

-- Pengajuan pending yang sudah ada sebelum migration ikut menahan kuota.
UPDATE trip_schedules ts
LEFT JOIN (
  SELECT schedule_id, SUM(participants) reserved
  FROM (
    SELECT schedule_id, participants FROM bookings
    WHERE schedule_id IS NOT NULL AND status IN ('Menunggu Approval','Disetujui','Selesai')
    UNION ALL
    SELECT r.requested_schedule_id, b.participants
    FROM booking_reschedule_requests r
    INNER JOIN bookings b ON b.id = r.booking_id
    WHERE r.requested_schedule_id IS NOT NULL AND r.status = 'pending' AND b.status = 'Disetujui'
  ) holds
  GROUP BY schedule_id
) totals ON totals.schedule_id = ts.id
SET ts.booked_count = COALESCE(totals.reserved, 0),
    ts.status = CASE
      WHEN ts.status = 'inactive' THEN 'inactive'
      WHEN ts.quota <= COALESCE(totals.reserved, 0) THEN 'full'
      ELSE 'active'
    END;

UPDATE trips t
INNER JOIN (
  SELECT trip_id, SUM(quota) quota, SUM(GREATEST(quota - booked_count, 0)) slots
  FROM trip_schedules GROUP BY trip_id
) totals ON totals.trip_id = t.id
SET t.quota = totals.quota,
    t.slots = totals.slots,
    t.status = CASE
      WHEN t.status IN ('Ditutup','Selesai') THEN t.status
      WHEN totals.slots <= 0 THEN 'Penuh'
      ELSE 'Tersedia'
    END
WHERE t.trip_type = 'open';
