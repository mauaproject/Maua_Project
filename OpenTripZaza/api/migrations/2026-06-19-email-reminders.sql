-- Jalankan satu kali melalui phpMyAdmin sebelum mengaktifkan cron reminder.

ALTER TABLE bookings
    ADD COLUMN visible_until DATE NULL AFTER selected_date,
    ADD COLUMN archived_at DATETIME NULL AFTER visible_until,
    ADD KEY idx_bookings_active_retention (archived_at, visible_until),
    ADD KEY idx_bookings_reminder_date (selected_date, status);

UPDATE bookings
SET visible_until = DATE_ADD(selected_date, INTERVAL 7 DAY)
WHERE selected_date IS NOT NULL
  AND visible_until IS NULL;

ALTER TABLE trip_schedules
    ADD COLUMN visible_until DATE NULL AFTER schedule_date,
    ADD COLUMN archived_at DATETIME NULL AFTER visible_until,
    ADD KEY idx_trip_schedules_active_retention (archived_at, visible_until);

UPDATE trip_schedules
SET visible_until = DATE_ADD(schedule_date, INTERVAL 7 DAY)
WHERE schedule_date IS NOT NULL
  AND visible_until IS NULL;

CREATE TABLE IF NOT EXISTS reminder_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id BIGINT UNSIGNED NOT NULL,
    reminder_type ENUM('H7','H1','HPLUS1') NOT NULL,
    sent_at DATETIME NULL,
    email_to VARCHAR(190) NOT NULL,
    status ENUM('processing','success','failed') NOT NULL DEFAULT 'processing',
    error_message TEXT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reminder_booking_type (booking_id, reminder_type),
    KEY idx_reminder_status (status, updated_at),
    CONSTRAINT fk_reminder_logs_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
