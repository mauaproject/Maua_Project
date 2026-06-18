CREATE TABLE IF NOT EXISTS trip_addons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    trip_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    worker_action ENUM('drive_link','none') NOT NULL DEFAULT 'none',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_trip_addons_trip (trip_id),
    CONSTRAINT fk_trip_addons_trip
        FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE booking_addons
    MODIFY addon_id VARCHAR(50) NULL,
    ADD COLUMN trip_addon_id BIGINT UNSIGNED NULL AFTER addon_id,
    ADD KEY idx_booking_addons_trip_addon (trip_addon_id),
    ADD CONSTRAINT fk_booking_addons_trip_addon
        FOREIGN KEY (trip_addon_id) REFERENCES trip_addons(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE worker_tasks
    MODIFY addon_id VARCHAR(50) NULL,
    ADD COLUMN trip_addon_id BIGINT UNSIGNED NULL AFTER addon_id,
    ADD COLUMN addon_name VARCHAR(150) NULL AFTER trip_addon_id,
    ADD COLUMN worker_action ENUM('drive_link','none') NOT NULL DEFAULT 'none' AFTER addon_name,
    ADD KEY idx_worker_tasks_trip_addon (trip_addon_id),
    ADD CONSTRAINT fk_worker_tasks_trip_addon
        FOREIGN KEY (trip_addon_id) REFERENCES trip_addons(id)
        ON DELETE SET NULL ON UPDATE CASCADE;
