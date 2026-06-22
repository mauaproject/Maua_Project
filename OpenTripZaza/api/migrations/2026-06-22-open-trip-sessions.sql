-- Jalankan satu kali melalui phpMyAdmin.
-- Setiap baris trip_schedules mewakili satu sesi keberangkatan.

ALTER TABLE trip_schedules
    ADD COLUMN session_name VARCHAR(100) NOT NULL DEFAULT 'Sesi 1' AFTER schedule_code;

UPDATE trip_schedules
SET session_name = 'Sesi 1'
WHERE session_name IS NULL OR TRIM(session_name) = '';
