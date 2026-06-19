-- Jalankan satu kali setelah migrasi email verification sebelumnya.
-- Data signup disimpan sementara di sini dan baru dipindahkan ke users setelah OTP benar.

CREATE TABLE IF NOT EXISTS pending_customer_registrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(50) NULL,
    address TEXT NULL,
    age INT NULL,
    gender VARCHAR(30) NULL,
    health_notes TEXT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expired_at DATETIME NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_sent_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pending_customer_email (email),
    KEY idx_pending_customer_expiry (expired_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
