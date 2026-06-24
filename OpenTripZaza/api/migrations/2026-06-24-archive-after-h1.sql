-- Jalankan satu kali setelah deploy perubahan arsip H+1.
-- Migrasi ini menyesuaikan data lama yang sebelumnya memakai visible_until H+7.

UPDATE bookings
SET visible_until = DATE_ADD(selected_date, INTERVAL 1 DAY)
WHERE selected_date IS NOT NULL;

UPDATE trip_schedules
SET visible_until = DATE_ADD(schedule_date, INTERVAL 1 DAY)
WHERE schedule_date IS NOT NULL;

UPDATE bookings
SET archived_at = COALESCE(archived_at, NOW())
WHERE DATE_ADD(
    TIMESTAMP(COALESCE(selected_date, DATE(created_at)), COALESCE(end_time, '23:59:59')),
    INTERVAL 1 DAY
) < NOW();

UPDATE trip_schedules
SET archived_at = COALESCE(archived_at, NOW()),
    status = CASE WHEN status = 'inactive' THEN status ELSE 'inactive' END
WHERE DATE_ADD(
    TIMESTAMP(schedule_date, COALESCE(end_time, '23:59:59')),
    INTERVAL 1 DAY
) < NOW();
