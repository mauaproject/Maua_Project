ALTER TABLE reviews
    DROP FOREIGN KEY fk_reviews_booking;

ALTER TABLE reviews
    DROP FOREIGN KEY fk_reviews_trip;

ALTER TABLE reviews
    MODIFY booking_id BIGINT UNSIGNED NULL,
    MODIFY trip_id BIGINT UNSIGNED NULL,
    ADD COLUMN trip_label VARCHAR(190) NULL AFTER trip_id;

ALTER TABLE reviews
    ADD CONSTRAINT fk_reviews_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_reviews_trip
        FOREIGN KEY (trip_id) REFERENCES trips(id)
        ON DELETE CASCADE ON UPDATE CASCADE;
