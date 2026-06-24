-- Jalankan sekali setelah deploy revisi kuota booking.
-- Migration ini menambahkan status pelepas slot dan menyinkronkan ulang slot open trip.

ALTER TABLE bookings
    MODIFY status ENUM('Menunggu Approval','Disetujui','Ditolak','Dibatalkan','Expired','Selesai')
    NOT NULL DEFAULT 'Menunggu Approval';

UPDATE trip_schedules ts
LEFT JOIN (
    SELECT
        schedule_id,
        COALESCE(SUM(participants), 0) AS reserved_count
    FROM bookings
    WHERE schedule_id IS NOT NULL
      AND status IN ('Menunggu Approval','Disetujui','Selesai')
    GROUP BY schedule_id
) b ON b.schedule_id = ts.id
SET
    ts.booked_count = COALESCE(b.reserved_count, 0),
    ts.status = CASE
        WHEN ts.status = 'inactive' THEN 'inactive'
        WHEN ts.quota <= COALESCE(b.reserved_count, 0) THEN 'full'
        ELSE 'active'
    END;

UPDATE trips t
JOIN (
    SELECT
        trip_id,
        COALESCE(SUM(quota), 0) AS total_quota,
        COALESCE(SUM(GREATEST(quota - booked_count, 0)), 0) AS total_slots
    FROM trip_schedules
    GROUP BY trip_id
) s ON s.trip_id = t.id
SET
    t.quota = s.total_quota,
    t.slots = s.total_slots,
    t.status = CASE
        WHEN t.status IN ('Ditutup','Selesai') THEN t.status
        WHEN s.total_slots <= 0 THEN 'Penuh'
        ELSE 'Tersedia'
    END
WHERE t.trip_type = 'open';
