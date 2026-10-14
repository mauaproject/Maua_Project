-- Jalankan sekali lewat phpMyAdmin setelah template private Goa Grubug terpasang.
-- Memperbaiki kapasitas mobil pada trip sumber template. Generator menyalinnya
-- otomatis ke setiap paket baru: 1-5 peserta = 1 mobil, 6-10 = 2, dst.
-- Paket yang sudah di-generate dan booking lama tidak diubah.
-- Aman dijalankan ulang; tidak menambah add-on atau mengubah harga.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER $$
DROP PROCEDURE IF EXISTS fix_grubug_template_transport_20261004$$
CREATE PROCEDURE fix_grubug_template_transport_20261004()
BEGIN
  DECLARE source_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SET source_id = (
    SELECT g.source_trip_id
    FROM trip_generation_templates g
    INNER JOIN trips t ON t.id = g.source_trip_id
    WHERE g.template_key = 'private-goa-grubug'
      AND g.trip_type = 'private' AND t.trip_type = 'private'
    LIMIT 1
  );
  IF source_id IS NULL THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Template private Goa Grubug belum ada. Jalankan migration 2026-10-02-private-goa-grubug-template.sql terlebih dahulu.';
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM trip_addons
    WHERE trip_id = source_id
      AND TRIM(name) = 'Transportasi A - Mobil maksimal 5 orang'
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Add-on Transportasi A - Mobil maksimal 5 orang tidak ditemukan pada sumber template Goa Grubug. Periksa nama add-on.';
  END IF;

  UPDATE trip_addons
  SET max_participants_per_unit = 5, updated_at = CURRENT_TIMESTAMP
  WHERE trip_id = source_id
    AND TRIM(name) = 'Transportasi A - Mobil maksimal 5 orang'
    AND (max_participants_per_unit IS NULL OR max_participants_per_unit <> 5);

  COMMIT;

  SELECT 'Kapasitas mobil template Goa Grubug sudah 5 peserta per unit; berlaku untuk paket yang di-generate berikutnya.' AS result,
         trip_id AS source_trip_id, name, price, max_participants_per_unit
  FROM trip_addons
  WHERE trip_id = source_id
    AND TRIM(name) = 'Transportasi A - Mobil maksimal 5 orang';
END$$
CALL fix_grubug_template_transport_20261004()$$
DROP PROCEDURE fix_grubug_template_transport_20261004$$
DELIMITER ;
