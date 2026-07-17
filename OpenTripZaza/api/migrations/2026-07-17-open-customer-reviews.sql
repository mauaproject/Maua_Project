ALTER TABLE reviews
    DROP FOREIGN KEY fk_reviews_booking;

ALTER TABLE reviews
    MODIFY booking_id BIGINT UNSIGNED NULL;

ALTER TABLE reviews
    ADD CONSTRAINT fk_reviews_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE;
