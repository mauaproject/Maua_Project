-- Jalankan satu kali melalui phpMyAdmin sebelum memakai template H-7 per trip.

ALTER TABLE trips
    ADD COLUMN h7_reminder_subject VARCHAR(190) NULL AFTER flexible_schedule,
    ADD COLUMN h7_reminder_body LONGTEXT NULL AFTER h7_reminder_subject;
