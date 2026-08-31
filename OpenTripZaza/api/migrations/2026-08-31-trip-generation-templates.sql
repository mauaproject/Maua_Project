-- Template generator paket trip bulanan.
-- Tabel ini hanya menambah metadata template/generasi dan tidak mengubah paket lama.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `trip_generation_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_key` varchar(80) NOT NULL,
  `source_trip_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `trip_type` enum('open','private') NOT NULL,
  `pattern_label` varchar(255) NOT NULL,
  `schedule_pattern_json` longtext DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trip_generation_template_key` (`template_key`),
  KEY `idx_trip_generation_template_source` (`source_trip_id`),
  KEY `idx_trip_generation_template_type_status` (`trip_type`,`status`,`sort_order`),
  CONSTRAINT `fk_trip_generation_template_source`
    FOREIGN KEY (`source_trip_id`) REFERENCES `trips` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trip_monthly_generations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `period_year` smallint(5) UNSIGNED NOT NULL,
  `period_month` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trip_monthly_generation_period` (`template_id`,`period_year`,`period_month`),
  UNIQUE KEY `uq_trip_monthly_generation_trip` (`trip_id`),
  KEY `idx_trip_monthly_generation_period` (`period_year`,`period_month`),
  CONSTRAINT `fk_trip_monthly_generation_template`
    FOREIGN KEY (`template_id`) REFERENCES `trip_generation_templates` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_trip_monthly_generation_trip`
    FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `chk_trip_monthly_generation_month`
    CHECK (`period_month` BETWEEN 1 AND 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trip_generation_templates`
  (`template_key`, `source_trip_id`, `name`, `trip_type`, `pattern_label`, `schedule_pattern_json`, `status`, `sort_order`)
SELECT
  config.template_key,
  t.id,
  config.template_name,
  config.trip_type,
  config.pattern_label,
  config.schedule_pattern_json,
  'active',
  config.sort_order
FROM (
  SELECT
    'open-goa-ngeleng' AS template_key,
    'Goa Ngeleng' AS source_name,
    'Goa Ngeleng' AS template_name,
    'open' AS trip_type,
    'Setiap Minggu dua sesi, ditambah Senin ke-2' AS pattern_label,
    '{"codePrefix":"GOA_NGELENG","rules":[{"type":"nth_weekday","weekday":0,"nth":1,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":4},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:30","quota":6}]},{"type":"nth_weekday","weekday":0,"nth":2,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":4},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:30","quota":5}]},{"type":"nth_weekday","weekday":0,"nth":3,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":5},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:30","quota":5}]},{"type":"nth_weekday","weekday":0,"nth":4,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":5},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:30","quota":9}]},{"type":"nth_weekday","weekday":0,"nth":5,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":5},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:30","quota":9}]},{"type":"nth_weekday","weekday":1,"nth":2,"sessions":[{"code":"SESI9","name":"Sesi 9","startTime":"07:30","endTime":"12:30","quota":5}]}]}' AS schedule_pattern_json,
    10 AS sort_order
  UNION ALL SELECT
    'open-goa-sumitro', 'Goa Sumitro', 'Goa Sumitro', 'open',
    'Minggu ke-2 dan Minggu ke-4',
    '{"codePrefix":"GOA_SUMITRO","rules":[{"type":"nth_weekday","weekday":0,"nth":2,"sessions":[{"code":"SESI1","name":"Sesi 1","startTime":"08:00","endTime":"13:00","quota":5}]},{"type":"nth_weekday","weekday":0,"nth":4,"sessions":[{"code":"SESI2","name":"Sesi 2","startTime":"08:00","endTime":"13:00","quota":8}]}]}',
    20
  UNION ALL SELECT
    'open-goa-macan-mati', 'Goa Macan Mati', 'Goa Macan Mati', 'open',
    'Minggu ke-4',
    '{"codePrefix":"GOA_MACAN_MATI","rules":[{"type":"nth_weekday","weekday":0,"nth":4,"sessions":[{"code":"SESI1","name":"Sesi 1","startTime":"08:00","endTime":"14:00","quota":5}]}]}',
    30
  UNION ALL SELECT
    'open-goa-jomblang', 'Goa Jomblang', 'Goa Jomblang', 'open',
    'Setiap hari',
    '{"codePrefix":"JOMBLANG","rules":[{"type":"daily","sessions":[{"code":"SESI1","name":"Sesi 1","startTime":"08:00","endTime":"13:00","quota":20}]}]}',
    40
  UNION ALL SELECT
    'open-paddle-board', 'Paddle Board', 'Paddle Board', 'open',
    'Sabtu ke-2, Sabtu ke-3, dan Minggu ke-3',
    '{"codePrefix":"PADDLE_BOARD","rules":[{"type":"nth_weekday","weekday":6,"nth":2,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":6},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:00","quota":6}]},{"type":"nth_weekday","weekday":6,"nth":3,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":6},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:00","quota":6}]},{"type":"nth_weekday","weekday":0,"nth":3,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"12:00","quota":6},{"code":"SIANG","name":"Sesi Siang","startTime":"13:30","endTime":"17:00","quota":6}]}]}',
    50
  UNION ALL SELECT
    'private-goa-macan-mati', 'Goa Macan Mati', 'Goa Macan Mati', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 110
  UNION ALL SELECT
    'private-goa-sumitro', 'Goa Sumitro', 'Goa Sumitro', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 120
  UNION ALL SELECT
    'private-paddle-board', 'Paddle Board', 'Paddle Board', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 130
  UNION ALL SELECT
    'private-lava-tour-merapi', 'Lava Tour Merapi', 'Lava Tour Merapi', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 140
  UNION ALL SELECT
    'private-cave-tubbing-kalisuci', 'Cave Tubbing - Kalisuci', 'Cave Tubbing - Kalisuci', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 150
  UNION ALL SELECT
    'private-goa-ngeleng', 'Goa Ngeleng', 'Goa Ngeleng', 'private',
    'Tersedia setiap tanggal dalam bulan terpilih', NULL, 160
) config
INNER JOIN (
  SELECT
    `name`,
    `trip_type`,
    COALESCE(MAX(CASE WHEN `status` = 'Tersedia' THEN `id` END), MAX(`id`)) AS `source_id`
  FROM `trips`
  GROUP BY `name`, `trip_type`
) source_match
  ON source_match.name = config.source_name
  AND source_match.trip_type = config.trip_type
INNER JOIN `trips` t ON t.id = source_match.source_id
ON DUPLICATE KEY UPDATE
  `source_trip_id` = VALUES(`source_trip_id`),
  `name` = VALUES(`name`),
  `trip_type` = VALUES(`trip_type`),
  `pattern_label` = VALUES(`pattern_label`),
  `schedule_pattern_json` = VALUES(`schedule_pattern_json`),
  `sort_order` = VALUES(`sort_order`);

-- Tandai paket Agustus 2026 yang sudah dibuat oleh migration sebelumnya agar
-- generator tidak dapat membuat duplikat untuk periode yang sama.
INSERT IGNORE INTO `trip_monthly_generations`
  (`template_id`, `trip_id`, `period_year`, `period_month`)
SELECT template.id, august_trip.id, 2026, 8
FROM `trip_generation_templates` template
INNER JOIN `trips` august_trip
  ON august_trip.name = CONCAT(template.name, ' - Agustus')
  AND august_trip.trip_type = template.trip_type;

COMMIT;
