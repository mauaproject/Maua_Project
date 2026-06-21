-- Jalankan satu kali melalui phpMyAdmin.
-- Index berikut mengikuti filter/join utama pada katalog, jadwal, booking, dan worker.

ALTER TABLE trips
    ADD KEY idx_trips_catalog (status, trip_type, id);

ALTER TABLE trip_schedules
    ADD KEY idx_trip_schedules_lookup (trip_id, status, schedule_date);

ALTER TABLE bookings
    ADD KEY idx_bookings_user_status (user_id, status, id),
    ADD KEY idx_bookings_trip_status_date (trip_id, status, selected_date);

ALTER TABLE worker_tasks
    ADD KEY idx_worker_tasks_worker_status (worker_id, status, id),
    ADD KEY idx_worker_tasks_booking_status (booking_id, status);
