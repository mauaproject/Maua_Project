-- Jalankan setelah 2026-06-19-private-trip-packages.sql.

ALTER TABLE private_trip_packages
    ADD COLUMN max_custom_pax INT UNSIGNED NOT NULL DEFAULT 1 AFTER price;

CREATE TABLE IF NOT EXISTS package_price_tiers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    package_id BIGINT UNSIGNED NOT NULL,
    pax_count INT UNSIGNED NOT NULL,
    price_per_person DECIMAL(14,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_package_price_tier (package_id, pax_count),
    CONSTRAINT fk_package_price_tiers_package
        FOREIGN KEY (package_id) REFERENCES private_trip_packages(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bookings
    ADD COLUMN selected_package_subtotal DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER selected_package_price;
