-- Jadwal open trip Agustus 2026 dibuat sebagai paket trip baru.
-- Pola mengikuti jadwal Juli 2026, dengan data operasional baru:
-- booked_count = 0, status = active, drive_link_url = NULL, archived_at = NULL.

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS `_august_open_trip_sources` (
  `source_id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `august_name` varchar(190) NOT NULL
);

DELETE FROM `_august_open_trip_sources`;

INSERT INTO `_august_open_trip_sources` (`source_id`, `august_name`) VALUES
  (33, 'Goa Ngeleng - Agustus'),
  (34, 'Goa Sumitro - Agustus'),
  (35, 'Goa Macan Mati - Agustus'),
  (36, 'Goa Jomblang - Agustus'),
  (37, 'Paddle Board - Agustus');

INSERT INTO `trips`
  (`name`, `trip_type`, `experience_type`, `status`, `destination_id`, `destination_en`, `description_id`, `description_en`, `activities_id`, `activities_en`, `facilities_id`, `facilities_en`, `price`, `quota`, `slots`, `min_participants`, `max_participants`, `max_custom_pax`, `available_start_date`, `available_end_date`, `private_notes`, `private_notes_en`, `flexible_schedule`, `private_booking_mode`, `include_drive_link`, `h7_reminder_subject`, `h7_reminder_body`, `created_at`, `updated_at`)
SELECT
  s.august_name,
  t.trip_type,
  t.experience_type,
  t.status,
  t.destination_id,
  t.destination_en,
  t.description_id,
  t.description_en,
  t.activities_id,
  t.activities_en,
  t.facilities_id,
  t.facilities_en,
  t.price,
  t.quota,
  t.slots,
  t.min_participants,
  t.max_participants,
  t.max_custom_pax,
  t.available_start_date,
  t.available_end_date,
  t.private_notes,
  t.private_notes_en,
  t.flexible_schedule,
  t.private_booking_mode,
  t.include_drive_link,
  t.h7_reminder_subject,
  t.h7_reminder_body,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `_august_open_trip_sources` s
INNER JOIN `trips` t ON t.id = s.source_id
WHERE t.trip_type = 'open'
  AND t.status = 'Tersedia'
  AND NOT EXISTS (
    SELECT 1
    FROM `trips` existing
    WHERE existing.trip_type = 'open'
      AND existing.name = s.august_name
  );

CREATE TEMPORARY TABLE IF NOT EXISTS `_august_open_trip_map` (
  `source_id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `target_id` bigint(20) UNSIGNED NOT NULL
);

DELETE FROM `_august_open_trip_map`;

INSERT INTO `_august_open_trip_map` (`source_id`, `target_id`)
SELECT s.source_id, t.id
FROM `_august_open_trip_sources` s
INNER JOIN `trips` t ON t.name = s.august_name AND t.trip_type = 'open';

INSERT INTO `trip_addons`
  (`trip_id`, `name`, `price`, `max_participants_per_unit`, `worker_action`, `status`, `sort_order`, `created_at`, `updated_at`)
SELECT
  m.target_id,
  src.name,
  src.price,
  src.max_participants_per_unit,
  src.worker_action,
  src.status,
  src.sort_order,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `trip_addons` src
INNER JOIN `_august_open_trip_map` m ON m.source_id = src.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `trip_addons` existing
  WHERE existing.trip_id = m.target_id
    AND existing.name = src.name
);

INSERT INTO `trip_schedules`
  (`trip_id`, `schedule_code`, `session_name`, `schedule_date`, `start_time`, `end_time`, `drive_link_url`, `visible_until`, `archived_at`, `quota`, `booked_count`, `status`, `created_at`)
SELECT
  m.target_id,
  v.schedule_code,
  v.session_name,
  v.schedule_date,
  v.start_time,
  v.end_time,
  NULL,
  DATE_ADD(v.schedule_date, INTERVAL 1 DAY),
  NULL,
  v.quota,
  0,
  'active',
  CURRENT_TIMESTAMP
FROM (
  -- Goa Jomblang: setiap hari, 1-31 Agustus.
  SELECT 36 AS trip_id, 'JOMBLANG-W1-20260801' AS schedule_code, 'Sesi 1' AS session_name, DATE '2026-08-01' AS schedule_date, TIME '08:00:00' AS start_time, TIME '13:00:00' AS end_time, 20 AS quota
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260802', 'Sesi 1', DATE '2026-08-02', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260803', 'Sesi 1', DATE '2026-08-03', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260804', 'Sesi 1', DATE '2026-08-04', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260805', 'Sesi 1', DATE '2026-08-05', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260806', 'Sesi 1', DATE '2026-08-06', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W1-20260807', 'Sesi 1', DATE '2026-08-07', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260808', 'Sesi 1', DATE '2026-08-08', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260809', 'Sesi 1', DATE '2026-08-09', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260810', 'Sesi 1', DATE '2026-08-10', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260811', 'Sesi 1', DATE '2026-08-11', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260812', 'Sesi 1', DATE '2026-08-12', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260813', 'Sesi 1', DATE '2026-08-13', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W2-20260814', 'Sesi 1', DATE '2026-08-14', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260815', 'Sesi 1', DATE '2026-08-15', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260816', 'Sesi 1', DATE '2026-08-16', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260817', 'Sesi 1', DATE '2026-08-17', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260818', 'Sesi 1', DATE '2026-08-18', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260819', 'Sesi 1', DATE '2026-08-19', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260820', 'Sesi 1', DATE '2026-08-20', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W3-20260821', 'Sesi 1', DATE '2026-08-21', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260822', 'Sesi 1', DATE '2026-08-22', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260823', 'Sesi 1', DATE '2026-08-23', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260824', 'Sesi 1', DATE '2026-08-24', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260825', 'Sesi 1', DATE '2026-08-25', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260826', 'Sesi 1', DATE '2026-08-26', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260827', 'Sesi 1', DATE '2026-08-27', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260828', 'Sesi 1', DATE '2026-08-28', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260829', 'Sesi 1', DATE '2026-08-29', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260830', 'Sesi 1', DATE '2026-08-30', TIME '08:00:00', TIME '13:00:00', 20
  UNION ALL SELECT 36, 'JOMBLANG-W4-20260831', 'Sesi 1', DATE '2026-08-31', TIME '08:00:00', TIME '13:00:00', 20

  -- Goa Sumitro: Minggu ke-2 dan Minggu ke-4, mengikuti 12 dan 26 Juli.
  UNION ALL SELECT 34, 'GOA_SUMITRO-20260809', 'Sesi 1', DATE '2026-08-09', TIME '08:00:00', TIME '13:00:00', 5
  UNION ALL SELECT 34, 'GOA_SUMITRO-20260823', 'Sesi 2', DATE '2026-08-23', TIME '08:00:00', TIME '13:00:00', 8

  -- Goa Macan Mati: Minggu ke-4, mengikuti 26 Juli.
  UNION ALL SELECT 35, 'GOA_MACAN_MATI-20260823', 'Sesi 1', DATE '2026-08-23', TIME '08:00:00', TIME '14:00:00', 5

  -- Goa Ngeleng: setiap Minggu + tambahan Senin minggu ke-2, mengikuti pola Juli.
  UNION ALL SELECT 33, 'GOA_NGELENG-20260802-PAGI', 'Sesi Pagi', DATE '2026-08-02', TIME '08:00:00', TIME '12:00:00', 4
  UNION ALL SELECT 33, 'GOA_NGELENG-20260802-SIANG', 'Sesi Siang', DATE '2026-08-02', TIME '13:30:00', TIME '17:30:00', 6
  UNION ALL SELECT 33, 'GOA_NGELENG-20260809-PAGI', 'Sesi Pagi', DATE '2026-08-09', TIME '08:00:00', TIME '12:00:00', 4
  UNION ALL SELECT 33, 'GOA_NGELENG-20260809-SIANG', 'Sesi Siang', DATE '2026-08-09', TIME '13:30:00', TIME '17:30:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260810-SESI9', 'Sesi 9', DATE '2026-08-10', TIME '07:30:00', TIME '12:30:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260816-PAGI', 'Sesi Pagi', DATE '2026-08-16', TIME '08:00:00', TIME '12:00:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260816-SIANG', 'Sesi Siang', DATE '2026-08-16', TIME '13:30:00', TIME '17:30:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260823-PAGI', 'Sesi Pagi', DATE '2026-08-23', TIME '08:00:00', TIME '12:00:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260823-SIANG', 'Sesi Siang', DATE '2026-08-23', TIME '13:30:00', TIME '17:30:00', 9
  UNION ALL SELECT 33, 'GOA_NGELENG-20260830-PAGI', 'Sesi Pagi', DATE '2026-08-30', TIME '08:00:00', TIME '12:00:00', 5
  UNION ALL SELECT 33, 'GOA_NGELENG-20260830-SIANG', 'Sesi Siang', DATE '2026-08-30', TIME '13:30:00', TIME '17:30:00', 9

  -- Paddle Board: Sabtu minggu ke-2, Sabtu minggu ke-3, Minggu minggu ke-3.
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260808-PAGI', 'Sesi Pagi', DATE '2026-08-08', TIME '08:00:00', TIME '12:00:00', 6
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260808-SIANG', 'Sesi Siang', DATE '2026-08-08', TIME '13:30:00', TIME '17:00:00', 6
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260815-PAGI', 'Sesi Pagi', DATE '2026-08-15', TIME '08:00:00', TIME '12:00:00', 6
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260815-SIANG', 'Sesi Siang', DATE '2026-08-15', TIME '13:30:00', TIME '17:00:00', 6
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260816-PAGI', 'Sesi Pagi', DATE '2026-08-16', TIME '08:00:00', TIME '12:00:00', 6
  UNION ALL SELECT 37, 'PADDLE_BOARD-20260816-SIANG', 'Sesi Siang', DATE '2026-08-16', TIME '13:30:00', TIME '17:00:00', 6
) AS v
INNER JOIN `_august_open_trip_map` m ON m.source_id = v.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `trip_schedules` ts
  WHERE ts.trip_id = m.target_id
    AND ts.schedule_date = v.schedule_date
    AND ts.start_time = v.start_time
    AND ts.end_time = v.end_time
);

UPDATE `trips` t
INNER JOIN (
  SELECT
    trip_id,
    COALESCE(SUM(quota), 0) AS total_quota,
    COALESCE(SUM(GREATEST(quota - booked_count, 0)), 0) AS total_slots
  FROM `trip_schedules`
  WHERE trip_id IN (SELECT target_id FROM `_august_open_trip_map`)
  GROUP BY trip_id
) s ON s.trip_id = t.id
SET
  t.quota = s.total_quota,
  t.slots = s.total_slots,
  t.status = CASE
    WHEN t.status IN ('Ditutup', 'Selesai') THEN t.status
    WHEN s.total_slots <= 0 THEN 'Penuh'
    ELSE 'Tersedia'
  END;

-- Private trip Agustus dibuat sebagai paket baru, bukan memperpanjang paket lama.
-- Jomblang private single tidak diclone karena statusnya Ditutup.
CREATE TEMPORARY TABLE IF NOT EXISTS `_august_private_trip_sources` (
  `source_id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `august_name` varchar(190) NOT NULL
);

DELETE FROM `_august_private_trip_sources`;

INSERT INTO `_august_private_trip_sources` (`source_id`, `august_name`) VALUES
  (39, 'Goa Macan Mati - Agustus'),
  (40, 'Goa Sumitro - Agustus'),
  (42, 'Paddle Board - Agustus'),
  (43, 'Lava Tour Merapi - Agustus'),
  (44, 'Cave Tubbing - Kalisuci - Agustus'),
  (45, 'Goa Ngeleng - Agustus');

INSERT INTO `trips`
  (`name`, `trip_type`, `experience_type`, `status`, `destination_id`, `destination_en`, `description_id`, `description_en`, `activities_id`, `activities_en`, `facilities_id`, `facilities_en`, `price`, `quota`, `slots`, `min_participants`, `max_participants`, `max_custom_pax`, `available_start_date`, `available_end_date`, `private_notes`, `private_notes_en`, `flexible_schedule`, `private_booking_mode`, `include_drive_link`, `h7_reminder_subject`, `h7_reminder_body`, `created_at`, `updated_at`)
SELECT
  s.august_name,
  t.trip_type,
  t.experience_type,
  t.status,
  t.destination_id,
  t.destination_en,
  t.description_id,
  t.description_en,
  t.activities_id,
  t.activities_en,
  t.facilities_id,
  t.facilities_en,
  t.price,
  t.quota,
  t.slots,
  t.min_participants,
  t.max_participants,
  t.max_custom_pax,
  DATE '2026-08-01',
  DATE '2026-08-31',
  t.private_notes,
  CASE
    WHEN t.private_notes_en IS NULL THEN NULL
    ELSE REPLACE(t.private_notes_en, 'throughout July', 'throughout August')
  END,
  t.flexible_schedule,
  t.private_booking_mode,
  t.include_drive_link,
  t.h7_reminder_subject,
  t.h7_reminder_body,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `_august_private_trip_sources` s
INNER JOIN `trips` t ON t.id = s.source_id
WHERE t.trip_type = 'private'
  AND t.status = 'Tersedia'
  AND NOT EXISTS (
    SELECT 1
    FROM `trips` existing
    WHERE existing.trip_type = 'private'
      AND existing.name = s.august_name
  );

CREATE TEMPORARY TABLE IF NOT EXISTS `_august_private_trip_map` (
  `source_id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `target_id` bigint(20) UNSIGNED NOT NULL
);

DELETE FROM `_august_private_trip_map`;

INSERT INTO `_august_private_trip_map` (`source_id`, `target_id`)
SELECT s.source_id, t.id
FROM `_august_private_trip_sources` s
INNER JOIN `trips` t ON t.name = s.august_name AND t.trip_type = 'private';

INSERT INTO `trip_sessions`
  (`trip_id`, `session_code`, `name`, `start_time`, `end_time`, `drive_link_url`, `status`)
SELECT
  m.target_id,
  src.session_code,
  src.name,
  src.start_time,
  src.end_time,
  NULL,
  src.status
FROM `trip_sessions` src
INNER JOIN `_august_private_trip_map` m ON m.source_id = src.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `trip_sessions` existing
  WHERE existing.trip_id = m.target_id
    AND existing.session_code = src.session_code
);

INSERT INTO `private_price_tiers`
  (`trip_id`, `pax_count`, `price_per_person`)
SELECT
  m.target_id,
  src.pax_count,
  src.price_per_person
FROM `private_price_tiers` src
INNER JOIN `_august_private_trip_map` m ON m.source_id = src.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `private_price_tiers` existing
  WHERE existing.trip_id = m.target_id
    AND existing.pax_count = src.pax_count
);

INSERT INTO `trip_addons`
  (`trip_id`, `name`, `price`, `max_participants_per_unit`, `worker_action`, `status`, `sort_order`, `created_at`, `updated_at`)
SELECT
  m.target_id,
  src.name,
  src.price,
  src.max_participants_per_unit,
  src.worker_action,
  src.status,
  src.sort_order,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `trip_addons` src
INNER JOIN `_august_private_trip_map` m ON m.source_id = src.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `trip_addons` existing
  WHERE existing.trip_id = m.target_id
    AND existing.name = src.name
);

INSERT INTO `private_trip_packages`
  (`trip_id`, `package_code`, `name`, `name_en`, `price`, `max_custom_pax`, `destinations_json`, `destinations_json_en`, `description`, `description_en`, `status`, `sort_order`, `created_at`, `updated_at`)
SELECT
  m.target_id,
  src.package_code,
  src.name,
  src.name_en,
  src.price,
  src.max_custom_pax,
  src.destinations_json,
  src.destinations_json_en,
  src.description,
  src.description_en,
  src.status,
  src.sort_order,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `private_trip_packages` src
INNER JOIN `_august_private_trip_map` m ON m.source_id = src.trip_id
WHERE NOT EXISTS (
  SELECT 1
  FROM `private_trip_packages` existing
  WHERE existing.trip_id = m.target_id
    AND existing.package_code = src.package_code
);

INSERT INTO `package_price_tiers`
  (`package_id`, `pax_count`, `price_per_person`, `created_at`, `updated_at`)
SELECT
  target_package.id,
  src_tier.pax_count,
  src_tier.price_per_person,
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
FROM `package_price_tiers` src_tier
INNER JOIN `private_trip_packages` source_package ON source_package.id = src_tier.package_id
INNER JOIN `_august_private_trip_map` m ON m.source_id = source_package.trip_id
INNER JOIN `private_trip_packages` target_package
  ON target_package.trip_id = m.target_id
  AND target_package.package_code = source_package.package_code
WHERE NOT EXISTS (
  SELECT 1
  FROM `package_price_tiers` existing
  WHERE existing.package_id = target_package.id
    AND existing.pax_count = src_tier.pax_count
);

-- Paket private lama yang sebelumnya punya rentang melewati Agustus ditutup sampai Juli
-- agar tidak tampil dobel dengan paket "... - Agustus".
UPDATE `trips` t
INNER JOIN `_august_private_trip_sources` s ON s.source_id = t.id
SET
  t.available_end_date = DATE '2026-07-31',
  t.updated_at = CURRENT_TIMESTAMP
WHERE t.trip_type = 'private'
  AND t.status = 'Tersedia'
  AND t.available_start_date <= DATE '2026-07-31'
  AND (t.available_end_date IS NULL OR t.available_end_date > DATE '2026-07-31');

COMMIT;
