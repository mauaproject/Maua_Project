ALTER TABLE trip_addons
    ADD COLUMN max_participants_per_unit INT UNSIGNED NULL AFTER price;

ALTER TABLE trips
    ADD COLUMN include_drive_link TINYINT(1) NOT NULL DEFAULT 0 AFTER private_booking_mode;

ALTER TABLE trip_schedules
    ADD COLUMN drive_link_url TEXT NULL AFTER end_time;

ALTER TABLE trip_sessions
    ADD COLUMN drive_link_url TEXT NULL AFTER end_time;

CREATE TABLE IF NOT EXISTS trip_documentation_links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_id INT UNSIGNED NOT NULL,
    schedule_id INT UNSIGNED NULL,
    session_id INT UNSIGNED NULL,
    schedule_date DATE NULL,
    drive_link_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_trip_doc_open_schedule (schedule_id),
    UNIQUE KEY uniq_trip_doc_private_date (trip_id, session_id, schedule_date),
    KEY idx_trip_doc_trip (trip_id),
    CONSTRAINT fk_trip_doc_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    CONSTRAINT fk_trip_doc_schedule FOREIGN KEY (schedule_id) REFERENCES trip_schedules(id) ON DELETE CASCADE,
    CONSTRAINT fk_trip_doc_session FOREIGN KEY (session_id) REFERENCES trip_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
