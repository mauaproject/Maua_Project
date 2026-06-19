-- Jalankan satu kali melalui phpMyAdmin sebelum memakai flow pembayaran baru.

ALTER TABLE bookings
    ADD COLUMN payment_type VARCHAR(20) NULL AFTER total_price,
    ADD COLUMN payment_status VARCHAR(40) NULL AFTER payment_type,
    ADD COLUMN required_payment_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER payment_status,
    ADD COLUMN paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER required_payment_amount,
    ADD COLUMN bca_account_number VARCHAR(64) NULL AFTER paid_amount,
    ADD KEY idx_bookings_payment_status (payment_status);

ALTER TABLE payments
    MODIFY COLUMN payment_status VARCHAR(40) NOT NULL DEFAULT 'waiting_verification';
