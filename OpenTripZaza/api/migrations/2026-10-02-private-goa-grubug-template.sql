-- Template private Goa Grubug. Prasyarat:
--   2026-08-31-trip-generation-templates.sql dan seluruh schema trip terbaru.
-- Jalankan lewat phpMyAdmin pada database MAUA.
-- Membuat satu trip sumber berstatus Ditutup, tanpa periode dan media.
-- Paket aktif satu bulan penuh dibuat lewat Tambah Paket > Private Trip.
-- Aman dijalankan ulang: template yang sudah ada tidak diubah.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Meeting point dan jam mulai caving telah dikonfirmasi pemilik usaha.
SET @grubug_meeting_point_name = 'Goa Jomblang';
SET @grubug_meeting_point_url = 'https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic';

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_private_grubug_20261002$$
CREATE PROCEDURE seed_private_grubug_20261002()
seed: BEGIN
  DECLARE source_jomblang_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE grubug_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE template_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE reminder_body LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SET template_id = (
    SELECT id FROM trip_generation_templates
    WHERE template_key = 'private-goa-grubug'
    LIMIT 1
  );
  IF template_id IS NOT NULL THEN
    COMMIT;
    SELECT 'Template Goa Grubug sudah ada; tidak ada data yang diubah.' AS result;
    LEAVE seed;
  END IF;

  IF NULLIF(TRIM(COALESCE(@grubug_meeting_point_name, '')), '') IS NULL
     OR NULLIF(TRIM(COALESCE(@grubug_meeting_point_url, '')), '') IS NULL
     OR @grubug_meeting_point_url NOT LIKE 'https://%' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Isi nama meeting point Goa Grubug dan URL Google Maps HTTPS yang sudah dikonfirmasi.';
  END IF;

  IF EXISTS (
    SELECT 1 FROM trips WHERE trip_type = 'private' AND name = 'Goa Grubug'
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Private Goa Grubug sudah ada tanpa template. Periksa paket tersebut sebelum melanjutkan.';
  END IF;

  -- Utamakan sumber template Jomblang yang terdaftar, lalu paket dasar,
  -- lalu paket bulanan Jomblang terbaru. Hindari paket kombinasi destinasi.
  SET source_jomblang_id = COALESCE(
    (SELECT t.id FROM trip_generation_templates g
     INNER JOIN trips t ON t.id = g.source_trip_id
     WHERE g.template_key = 'private-goa-jomblang'
       AND t.trip_type = 'private'
       AND (t.name = 'Goa Jomblang' OR t.name LIKE 'Goa Jomblang - %')
     LIMIT 1),
    (SELECT t.id FROM trips t
     WHERE t.trip_type = 'private'
       AND (t.name = 'Goa Jomblang' OR t.name LIKE 'Goa Jomblang - %')
     ORDER BY (t.name = 'Goa Jomblang') DESC, t.id DESC
     LIMIT 1)
  );
  IF source_jomblang_id IS NULL THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Paket private Goa Jomblang tidak ditemukan; add-on belum dapat disalin.';
  END IF;
  IF NOT EXISTS (SELECT 1 FROM trip_addons WHERE trip_id = source_jomblang_id) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Paket private Goa Jomblang sumber belum memiliki add-on. Periksa sumber terlebih dahulu.';
  END IF;

  SET reminder_body = 'Hi, Sobat Maua 👋
Terima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Reguler Caving di Goa Grubug. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻

Untuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:
• Pakaian yang nyaman untuk aktivitas outdoor (hindari pakaian yang terlalu berat, memiliki tali panjang yang menjuntai, serta penggunaan rok atau gamis).
• Pakaian ganti dan perlengkapan mandi (medan saat ini cukup berlumpur).
• Jas hujan atau ponco.
• Hydropack/daypack/tas yang nyaman digunakan saat aktivitas (tidak disarankan menggunakan sling bag atau tas bahu karena dapat mengganggu pergerakan saat menggunakan tali).
• Peralatan dokumentasi pribadi apabila diperlukan.
• Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu.

Teknis Kegiatan Reguler Caving Goa Grubug
📍 Meeting Point: {meeting_point}
📆 Tanggal: {tanggal_trip}
🕑 Waktu: {jam_trip}

📌 Rundown Kegiatan
07.00 – Penjemputan tamu (bagi yang menggunakan layanan penjemputan)
09.00 – Batas maksimal kedatangan peserta di meeting point
09.10 – Registrasi ulang
09.30 – Kegiatan caving dimulai
11.00–11.30 – Perkiraan selesai caving, dilanjutkan bersih-bersih dan makan siang
13.00 – Kegiatan berakhir, sayonara

📢 Catatan Penting
• Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.
• Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.
• Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.
• Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.
• Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.

Salam hangat,
Maua Project Team 🌿';
  -- Hanya tiga placeholder email asli yang dibiarkan: tanggal, jam, sisa hari.
  -- Meeting point dimasukkan sebagai teks saat migration dijalankan.
  SET reminder_body = REPLACE(
    reminder_body, '{meeting_point}',
    CONCAT(TRIM(@grubug_meeting_point_name), ' (', TRIM(@grubug_meeting_point_url), ')')
  );

  INSERT INTO trips
    (name, trip_type, experience_type, status, destination_id, destination_en,
     description_id, description_en, activities_id, activities_en, facilities_id, facilities_en,
     price, quota, slots, min_participants, max_participants, max_custom_pax,
     available_start_date, available_end_date, private_notes, private_notes_en,
     flexible_schedule, private_booking_mode, include_drive_link,
     h7_reminder_subject, h7_reminder_body)
  VALUES
    ('Goa Grubug', 'private', 'cave', 'Ditutup', 'Goa Grubug', 'Grubug Cave',
     'Goa Grubug menawarkan pengalaman turun ke dalam sinkhole alami sedalam sekitar 100 meter tepat di spot Heaven’s Light menggunakan sistem hauling. Setelah mencapai dasar goa, peserta akan diajak menikmati fenomena Cahaya Surga dari dalam goa, yaitu sinar matahari yang menembus mulut goa dan menciptakan pemandangan yang spektakuler. Trip ini cocok bagi pemula maupun pecinta petualangan yang ingin merasakan sensasi eksplorasi goa dengan tetap didampingi oleh tim profesional dan mengutamakan standar keselamatan.',
     'Grubug Cave offers the experience of descending into a natural sinkhole approximately 100 meters deep, directly at the Heaven’s Light spot, using a hauling system. Upon reaching the cave floor, participants can enjoy the spectacular Heaven’s Light phenomenon, where sunlight streams through the cave opening and illuminates the space below. This trip is suitable for beginners and adventure enthusiasts who want to experience cave exploration with professional assistance and an emphasis on safety standards.',
     JSON_ARRAY('Vertical caving di spot Heaven’s Light, satu per satu.', 'Eksplorasi goa horizontal.', 'Pengambilan foto dan video di spot Light of Heaven.'),
     JSON_ARRAY('Vertical caving at the Heaven’s Light spot, one participant at a time.', 'Exploring the cave’s horizontal passages.', 'Taking photos and videos at the Light of Heaven spot.'),
     JSON_ARRAY('Pemandu bersertifikat (Guide).', 'Perlengkapan keselamatan berstandar internasional: helm dan sepatu boots.', 'Durasi caving sekitar 1,5–2 jam, dimulai pukul 09.30 WIB.', 'Makan siang dan air mineral setelah kegiatan.', 'Fasilitas pendopo dan toilet.'),
     JSON_ARRAY('Certified guide.', 'International-standard safety equipment: helmet and boots.', 'Approximately 1.5–2 hours of caving, starting at 09:30 WIB (UTC+7).', 'Lunch and mineral water after the activity.', 'Pavilion and toilet facilities.'),
     1000000, 40, 40, 1, 40, 3,
     NULL, NULL,
     'Tersedia setiap tanggal dalam bulan yang dipilih. Satu booking eksklusif per tanggal dan sesi.',
     'Available every day of the selected month. One exclusive booking per date and session.',
     1, 'exclusive', 0,
     'Reminder – Reguler Caving Goa Grubug | {tanggal_trip}', reminder_body);
  SET grubug_id = LAST_INSERT_ID();

  INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES
    (grubug_id, 1, 3000000),
    (grubug_id, 2, 1500000),
    (grubug_id, 3, 1000000);

  INSERT INTO trip_sessions
    (trip_id, session_code, name, start_time, end_time, drive_link_url, status)
  VALUES
    (grubug_id, 'SESI1', 'Sesi 1', '09:00:00', '13:00:00', NULL, 'active');

  INSERT INTO trip_addons
    (trip_id, name, price, max_participants_per_unit, worker_action, status, sort_order, created_at, updated_at)
  SELECT grubug_id, name, price, max_participants_per_unit, worker_action, status, sort_order,
         CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
  FROM trip_addons
  WHERE trip_id = source_jomblang_id
  ORDER BY sort_order, id;

  -- Tidak ada INSERT trip_images: media sumber dan paket hasil generate kosong.
  -- Tidak menyalin private_trip_packages Jomblang agar harga tier Grubug dipakai.
  INSERT INTO trip_generation_templates
    (template_key, source_trip_id, name, trip_type, pattern_label, schedule_pattern_json, status, sort_order)
  VALUES
    ('private-goa-grubug', grubug_id, 'Goa Grubug', 'private',
     'Tersedia setiap tanggal dalam bulan terpilih; Sesi 1 09.00–13.00 WIB; eksklusif',
     NULL, 'active', 170);

  COMMIT;
  SELECT 'Template private Goa Grubug berhasil dibuat.' AS result,
         grubug_id AS source_trip_id,
         source_jomblang_id AS addons_copied_from_trip_id;
END$$
CALL seed_private_grubug_20261002()$$
DROP PROCEDURE seed_private_grubug_20261002$$
DELIMITER ;

