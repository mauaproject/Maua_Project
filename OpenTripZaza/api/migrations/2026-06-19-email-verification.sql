-- Jalankan satu kali melalui phpMyAdmin.

ALTER TABLE users
    ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER email,
    ADD COLUMN email_verified_at DATETIME NULL AFTER email_verified,
    ADD KEY idx_users_email_verified (email, email_verified);

-- Admin dan worker tidak memakai checkout customer.
UPDATE users
SET email_verified = 1,
    email_verified_at = COALESCE(email_verified_at, NOW())
WHERE role IN ('admin', 'worker');

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expired_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email_verification_token_hash (token_hash),
    KEY idx_email_verification_user (user_id, used_at, expired_at),
    CONSTRAINT fk_email_verification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
