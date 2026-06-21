-- Jalankan satu kali untuk menambahkan data keselamatan dan equipment customer.
-- Semua kolom nullable agar user lama tetap dapat login dan melengkapi profil kemudian.

ALTER TABLE users
    ADD COLUMN blood_type VARCHAR(20) NULL AFTER health_notes,
    ADD COLUMN height_cm SMALLINT UNSIGNED NULL AFTER blood_type,
    ADD COLUMN weight_kg DECIMAL(5,2) UNSIGNED NULL AFTER height_cm,
    ADD COLUMN shoe_size DECIMAL(4,1) UNSIGNED NULL AFTER weight_kg;

ALTER TABLE pending_customer_registrations
    ADD COLUMN blood_type VARCHAR(20) NULL AFTER health_notes,
    ADD COLUMN height_cm SMALLINT UNSIGNED NULL AFTER blood_type,
    ADD COLUMN weight_kg DECIMAL(5,2) UNSIGNED NULL AFTER height_cm,
    ADD COLUMN shoe_size DECIMAL(4,1) UNSIGNED NULL AFTER weight_kg;
