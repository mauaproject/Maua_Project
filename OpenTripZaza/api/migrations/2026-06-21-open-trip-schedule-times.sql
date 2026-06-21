-- Jalankan satu kali melalui phpMyAdmin untuk menambahkan jam pada jadwal Open Trip.

ALTER TABLE trip_schedules
    ADD COLUMN start_time TIME NULL AFTER schedule_date,
    ADD COLUMN end_time TIME NULL AFTER start_time;
