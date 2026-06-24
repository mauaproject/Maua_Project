-- Jalankan satu kali untuk session login tahan refresh dan data peserta lengkap.

CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_sessions_token_hash (token_hash),
    KEY idx_user_sessions_user_expires (user_id, expires_at),
    CONSTRAINT fk_user_sessions_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE booking_participants
    ADD COLUMN email VARCHAR(190) NULL AFTER name,
    ADD COLUMN whatsapp VARCHAR(50) NULL AFTER email,
    ADD COLUMN blood_type VARCHAR(20) NULL AFTER health_notes,
    ADD COLUMN height_cm DECIMAL(5,2) NULL AFTER blood_type,
    ADD COLUMN weight_kg DECIMAL(6,2) NULL AFTER height_cm,
    ADD COLUMN shoe_size DECIMAL(4,1) NULL AFTER weight_kg;

UPDATE booking_participants bp
INNER JOIN bookings b ON b.id = bp.booking_id
LEFT JOIN users u ON u.id = b.user_id
SET bp.email = COALESCE(bp.email, b.customer_email),
    bp.whatsapp = COALESCE(bp.whatsapp, b.customer_whatsapp),
    bp.blood_type = COALESCE(bp.blood_type, u.blood_type),
    bp.height_cm = COALESCE(bp.height_cm, u.height_cm),
    bp.weight_kg = COALESCE(bp.weight_kg, u.weight_kg),
    bp.shoe_size = COALESCE(bp.shoe_size, u.shoe_size);
