ALTER TABLE booking_addons
    ADD COLUMN addon_name VARCHAR(150) NULL AFTER trip_addon_id,
    ADD COLUMN max_participants_per_unit INT UNSIGNED NULL AFTER price,
    ADD COLUMN worker_action ENUM('drive_link','none') NULL AFTER max_participants_per_unit;
