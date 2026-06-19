-- Jalankan satu kali melalui phpMyAdmin sebelum memakai Paket Private Trip.

CREATE TABLE IF NOT EXISTS private_trip_packages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    trip_id BIGINT UNSIGNED NOT NULL,
    package_code VARCHAR(80) NOT NULL,
    name VARCHAR(190) NOT NULL,
    price DECIMAL(14,2) NOT NULL DEFAULT 0,
    destinations_json LONGTEXT NOT NULL,
    description TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_private_package_code (trip_id, package_code),
    KEY idx_private_package_status (trip_id, status, sort_order),
    CONSTRAINT fk_private_packages_trip
        FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bookings
    ADD COLUMN selected_package_id BIGINT UNSIGNED NULL AFTER session_id,
    ADD COLUMN selected_package_name VARCHAR(190) NULL AFTER selected_package_id,
    ADD COLUMN selected_package_price DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER selected_package_name,
    ADD COLUMN selected_package_destinations LONGTEXT NULL AFTER selected_package_price,
    ADD KEY idx_bookings_selected_package (selected_package_id);
