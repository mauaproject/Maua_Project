-- Jalankan satu kali melalui phpMyAdmin untuk mengatur kapasitas booking Private Trip.
-- Nilai default "exclusive" mempertahankan perilaku lama: satu booking per sesi per tanggal.

ALTER TABLE trips
    ADD COLUMN private_booking_mode ENUM('exclusive','shared') NOT NULL DEFAULT 'exclusive'
    AFTER flexible_schedule;
