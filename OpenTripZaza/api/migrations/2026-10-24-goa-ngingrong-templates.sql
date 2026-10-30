-- Template Open Trip dan Private Trip Goa Ngingrong.
-- Prasyarat: 2026-08-31-trip-generation-templates.sql dan schema trip terbaru.
-- Jalankan seluruh file melalui tab SQL phpMyAdmin pada database website.
-- Membuat sumber Ditutup untuk masing-masing jenis trip; foto tetap kosong.
-- Open: setiap Minggu, Sesi Pagi 08.00-13.00 WIB, kuota 6 per sesi.
-- Private: setiap tanggal, 1-6 peserta; minimum biaya senilai 3 peserta.
-- Pengulangan hanya menambahkan template yang belum terdaftar.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_ngingrong_templates_20261024$$
CREATE PROCEDURE seed_ngingrong_templates_20261024()
BEGIN
  DECLARE kind_index INT DEFAULT 0;
  DECLARE trip_kind VARCHAR(7);
  DECLARE template_key_value VARCHAR(80);
  DECLARE existing_template_id BIGINT UNSIGamNED DEFAULT NULL;
  DECLARE ngingrong_source_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE description_id_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE description_en_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE activities_id_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE activities_en_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE facilities_id_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE facilities_en_json LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE open_schedule_pattern LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE reminder_body LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SET description_id_text = 'Goa Ngingrong menawarkan pengalaman eksplorasi gua yang beragam, mulai dari vertical caving dengan teknik tali untuk menuruni gua, hingga horizontal caving menyusuri lorong-lorong goa sepanjang kurang lebih 200 meter. Salah satu daya tariknya adalah zona gelap abadi yang tidak terjangkau cahaya matahari, serta keberadaan danau bawah tanah yang menambah keunikan pengalaman eksplorasi. Dengan keindahan formasi batuan karst dan suasana bawah tanah yang eksotis, Goa Ngingrong menjadi pilihan menarik bagi pencinta alam dan wisata minat khusus yang ingin merasakan petualangan berbeda di Yogyakarta.';
  SET description_en_text = 'Goa Ngingrong offers a diverse cave exploration experience, ranging from vertical caving, using ropes to descend into the cave, to horizontal caving through approximately 200 meters of natural cave passages. One of its main attractions is the eternal darkness zone, a section of the cave that sunlight can never reach, as well as an underground lake that adds to the uniqueness of the exploration. With its stunning karst rock formations and exotic underground atmosphere, Goa Ngingrong is an exciting destination for nature lovers and adventure seekers looking for a unique experience in Yogyakarta.';

  SET activities_id_json = JSON_ARRAY(
    '08.00 - 08.30 Prepare & simulasi',
    '09.00 - 10.30 Menuju entrance goa, descending 70 m ke mulut goa (entrance)',
    'Eksplorasi zona gelap abadi (goa horizontal 200 m), descending 20 m, fotografi di telaga pertama',
    '10.30 - 12.00 Kembali ke meeting point',
    '12.00 Selesai, makan siang');
  SET activities_en_json = JSON_ARRAY(
    '08:00 - 08:30 Preparation and practice',
    '09:00 - 10:30 Travel to the cave entrance and descend 70 m to the cave mouth',
    'Explore the eternal darkness zone (200 m of horizontal cave passages), descend 20 m, and take photos at the first underground lake',
    '10:30 - 12:00 Return to the meeting point',
    '12:00 Finish caving and have lunch');
  SET facilities_id_json = JSON_ARRAY(
    'Alat caving (coverall/wearpack, sepatu boot, helm, harness, carabiner, descender autostop, tali statis 100 m)',
    'Air mineral',
    'P3K standar',
    'Dokumentasi program (foto, video GoPro & drone)',
    'Penitipan barang',
    'Caving guide',
    'Transportasi dari meeting point menuju entrance goa (Land Rover)',
    'Perahu packraft, dayung, pelampung',
    'Makan siang');
  SET facilities_en_json = JSON_ARRAY(
    'Caving equipment (coveralls, rubber boots, helmet, harness, carabiner, auto-stop descender, and 100 m static rope)',
    'Mineral water',
    'Standard first aid kit',
    'Activity documentation (photos, GoPro video, and drone footage)',
    'Luggage storage',
    'Caving guide',
    'Transportation from the meeting point to the cave entrance (Land Rover)',
    'Packraft, paddle, and life jacket',
    'Lunch');

  -- Generator memakai aturan Minggu ke-1 sampai ke-5 agar mencakup seluruh Minggu.
  SET open_schedule_pattern = '{"codePrefix":"GOA_NGINGRONG","rules":[
    {"type":"nth_weekday","weekday":0,"nth":1,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":6}]},
    {"type":"nth_weekday","weekday":0,"nth":2,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":6}]},
    {"type":"nth_weekday","weekday":0,"nth":3,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":6}]},
    {"type":"nth_weekday","weekday":0,"nth":4,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":6}]},
    {"type":"nth_weekday","weekday":0,"nth":5,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":6}]}
  ]}';

  SET reminder_body = 'Hi, Sobat Maua 👋
Terima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving Goa Ngingrong. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻

Untuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:
• Pakaian ganti (untuk antisipasi kondisi basah).
• Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali).
• Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari.
• Air minum tambahan, disarankan menggunakan tumbler untuk mengurangi sampah plastik.
• Peralatan dokumentasi pribadi apabila diperlukan.
• Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu.

Teknis Kegiatan Vertical Caving Goa Ngingrong
📍 Meeting Point: Geosite Ngingrong (https://maps.app.goo.gl/wTu9Mp7s1UEuMCb7A?g_st=ic)
📆 Tanggal: {tanggal_trip}
🕑 Waktu: {jam_trip}

📌 Rundown Kegiatan
08.00 - 08.30 Prepare & simulasi
09.00 - 10.30 Menuju entrance goa, descending 70 m ke mulut goa (entrance)
Eksplorasi zona gelap abadi (goa horizontal 200 m), descending 20 m, fotografi di telaga pertama
10.30 - 12.00 Kembali ke meeting point
12.00 Selesai, makan siang

📢 Catatan Penting
• Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.
• Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.
• Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.
• Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.
• Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.

Salam hangat,
Maua Project Team 🌿';

  WHILE kind_index < 2 DO
    SET trip_kind = IF(kind_index = 0, 'open', 'private');
    SET template_key_value = CONCAT(trip_kind, '-goa-ngingrong');
    SET existing_template_id = (
      SELECT id FROM trip_generation_templates
      WHERE template_key = template_key_value
      LIMIT 1
    );

    IF existing_template_id IS NULL THEN
      -- Sumber khusus menjaga paket dan booking yang sudah ada tetap utuh.
      -- Min peserta teknis 1: istilah minimum 3 berarti minimum pembayaran.
      INSERT INTO trips
        (name, trip_type, experience_type, status, destination_id, destination_en,
         description_id, description_en, activities_id, activities_en, facilities_id, facilities_en,
         price, quota, slots, min_participants, max_participants, max_custom_pax,
         available_start_date, available_end_date, private_notes, private_notes_en,
         flexible_schedule, private_booking_mode, include_drive_link,
         h7_reminder_subject, h7_reminder_body)
      VALUES
        (CONCAT('Goa Ngingrong - Sumber Template ', IF(trip_kind = 'private', 'Private Trip', 'Open Trip')),
         trip_kind, 'cave', 'Ditutup', 'Goa Ngingrong', 'Ngingrong Cave',
         description_id_text, description_en_text, activities_id_json, activities_en_json,
         facilities_id_json, facilities_en_json,
         650000, 6, 6, 1, 6, IF(trip_kind = 'private', 3, 0),
         NULL, NULL,
         IF(trip_kind = 'private', 'Tersedia setiap tanggal dalam bulan yang dipilih. Satu booking eksklusif per tanggal dan sesi. Minimum biaya senilai 3 peserta (Rp1.950.000); 1-2 peserta tetap dapat memesan. Harga 3-6 peserta Rp650.000 per orang.', NULL),
         IF(trip_kind = 'private', 'Available every day of the selected month. One exclusive booking per date and session. Minimum charge is equivalent to 3 participants (IDR 1,950,000); bookings for 1-2 participants are welcome. For 3-6 participants, the price is IDR 650,000 per person.', NULL),
         IF(trip_kind = 'private', 1, 0), 'exclusive', 1,
         'Reminder – Vertical Caving Goa Ngingrong | {tanggal_trip}', reminder_body);
      SET ngingrong_source_id = LAST_INSERT_ID();

      IF trip_kind = 'private' THEN
        -- Harga tier ke-3 juga berlaku untuk 4-6 peserta pada backend dan frontend.
        INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES
          (ngingrong_source_id, 1, 1950000),
          (ngingrong_source_id, 2, 975000),
          (ngingrong_source_id, 3, 650000);

        INSERT INTO trip_sessions
          (trip_id, session_code, name, start_time, end_time, drive_link_url, status)
        VALUES
          (ngingrong_source_id, 'PAGI', 'Sesi Pagi', '08:00:00', '13:00:00', NULL, 'active');
      END IF;

      -- Camera dan baterai sekali per booking; mobil per kapasitas; ojek per peserta.
      -- Dokumentasi bawaan memakai Include Drive; keenam add-on selesai tanpa link.
      INSERT INTO trip_addons
        (trip_id, name, price, max_participants_per_unit, worker_action, status, sort_order, created_at, updated_at)
      VALUES
        (ngingrong_source_id, 'Camera Insta360 (unit camera, memori 32gb, monopod, 1 battery)', 250000, NULL, 'none', 'active', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (ngingrong_source_id, 'Baterai Drone tambahan (waktu terbang 20 menit)', 150000, NULL, 'none', 'active', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (ngingrong_source_id, 'Transportasi A - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 5 orang', 850000, 5, 'none', 'active', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (ngingrong_source_id, 'Transportasi B - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 3 orang', 650000, 3, 'none', 'active', 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (ngingrong_source_id, 'Ojek Trip Goa Ngingrong Only, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor)', 200000, 1, 'none', 'active', 4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (ngingrong_source_id, 'Ojek Trip Fullday, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor, stay 10 jam)', 400000, 1, 'none', 'active', 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

      -- Bulan/tahun dipilih saat generate; sumber tidak memiliki jadwal atau gambar publik.
      INSERT INTO trip_generation_templates
        (template_key, source_trip_id, name, trip_type, pattern_label, schedule_pattern_json, status, sort_order)
      VALUES
        (template_key_value, ngingrong_source_id, 'Goa Ngingrong', trip_kind,
         IF(trip_kind = 'private',
            'Tersedia setiap tanggal dalam bulan terpilih; Sesi Pagi 08.00-13.00 WIB; eksklusif; 1-6 peserta, minimum biaya senilai 3 peserta',
            'Setiap Minggu; Sesi Pagi 08.00-13.00 WIB; kuota 6 peserta per sesi'),
         IF(trip_kind = 'private', NULL, open_schedule_pattern), 'active', IF(trip_kind = 'private', 190, 60));
    END IF;

    SET kind_index = kind_index + 1;
  END WHILE;

  COMMIT;
  SELECT 'Template Goa Ngingrong siap digunakan.' AS result, template_key, source_trip_id
  FROM trip_generation_templates
  WHERE template_key IN ('open-goa-ngingrong', 'private-goa-ngingrong')
  ORDER BY trip_type;
END$$
CALL seed_ngingrong_templates_20261024()$$
DROP PROCEDURE seed_ngingrong_templates_20261024$$
DELIMITER ;
