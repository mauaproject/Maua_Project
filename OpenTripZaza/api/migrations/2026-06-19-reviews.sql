CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NULL,
    trip_id BIGINT UNSIGNED NULL,
    trip_label VARCHAR(190) NULL,
    reviewer_name VARCHAR(190) NOT NULL,
    reviewer_email VARCHAR(190) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    content VARCHAR(500) NOT NULL,
    status ENUM('approved','hidden','deleted') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_review_booking (booking_id),
    KEY idx_reviews_public (status, created_at),
    KEY idx_reviews_user (user_id, created_at),
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_trip
        FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
