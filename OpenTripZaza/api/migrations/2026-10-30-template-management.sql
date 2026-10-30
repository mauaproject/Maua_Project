-- Pengelolaan template lewat admin. Paket dan riwayat generasi tetap tersimpan
-- ketika template dihapus. Aman dijalankan ulang pada schema generator yang ada.
SET @maua_template_column_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trips' AND COLUMN_NAME = 'is_template_source'),
  'SELECT 1',
  'ALTER TABLE trips ADD COLUMN is_template_source TINYINT(1) NOT NULL DEFAULT 0 AFTER status'
);
PREPARE maua_template_column FROM @maua_template_column_sql;
EXECUTE maua_template_column;
DEALLOCATE PREPARE maua_template_column;

SET @maua_template_fk_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'trip_monthly_generations' AND CONSTRAINT_NAME = 'fk_trip_monthly_generation_template'),
  'ALTER TABLE trip_monthly_generations DROP FOREIGN KEY fk_trip_monthly_generation_template',
  'SELECT 1'
);
PREPARE maua_template_fk FROM @maua_template_fk_sql;
EXECUTE maua_template_fk;
DEALLOCATE PREPARE maua_template_fk;

ALTER TABLE trip_monthly_generations
  MODIFY template_id BIGINT(20) UNSIGNED NULL,
  ADD CONSTRAINT fk_trip_monthly_generation_template
    FOREIGN KEY (template_id) REFERENCES trip_generation_templates (id)
    ON UPDATE CASCADE ON DELETE SET NULL;
