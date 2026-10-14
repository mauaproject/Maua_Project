-- Template private Treking - Gunung Api Purba Nglanggeran.
-- Prasyarat: 2026-08-31-trip-generation-templates.sql dan schema trip terbaru.
-- Salin seluruh isi file ke tab SQL phpMyAdmin pada database website; jalankan sekali.
-- Membuat trip sumber Ditutup dan template aktif, tanpa mengubah paket lama.
-- Hasil generate tersedia setiap tanggal dalam bulan yang dipilih.
-- Konfirmasi client: 1-3 peserta/booking, sesi 07.00-11.30, ojek per peserta.
-- Aman dijalankan ulang: template yang sudah ada tidak ditimpa atau diduplikasi.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_private_nglanggeran_20261004$$
CREATE PROCEDURE seed_private_nglanggeran_20261004()
seed: BEGIN
  DECLARE nglanggeran_id BIGINT UNSIGNED DEFAULT NULL;
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
    WHERE template_key = 'private-treking-gunung-api-purba-nglanggeran'
    LIMIT 1
  );
  IF template_id IS NOT NULL THEN
    COMMIT;
    SELECT 'Template private Nglanggeran sudah ada; tidak ada data yang diubah.' AS result;
    LEAVE seed;
  END IF;

  SET reminder_body = 'Hi, Sobat Maua 👋
Terima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Treking - Gunung Api Purba Nglanggeran. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻

Untuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:
• Perlengkapan pribadi (topi, kacamata, hydropack, sepatu)
• Peralatan dokumentasi tambahan jika diperlukan.
• Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu.

Teknis Kegiatan Treking - Gunung Api Purba Nglanggeran
📍 Meeting Point: Loket Gunung Api Purba Nglanggeran (https://maps.app.goo.gl/EPBrnQy51DZ5tVKeA?g_st=ic)
📆 Tanggal: {tanggal_trip}
🕑 Waktu: {jam_trip}

📌 Rundown Kegiatan Sesi Pagi
06.30 - sampai di Meeting Point (Loket Gunung Api Purba Nglanggeran)
07.00 - briefing dan pemanasan
07.20 - mulai treking
08.20 - sampai di Puncak Bagong, take video drone
09.40 - sampai di puncak Gunung Api Purba
10.30 - treking turun
11.30 - selesai, sayonara

📢 Catatan Penting
• Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.
• Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.
• Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.
• Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.
• Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.

Salam hangat,
Maua Project Team 🌿';

  -- Sumber tersendiri; tidak menggunakan atau menimpa paket Nglanggeran yang sudah ada.
  INSERT INTO trips
    (name, trip_type, experience_type, status, destination_id, destination_en,
     description_id, description_en, activities_id, activities_en, facilities_id, facilities_en,
     price, quota, slots, min_participants, max_participants, max_custom_pax,
     available_start_date, available_end_date, private_notes, private_notes_en,
     flexible_schedule, private_booking_mode, include_drive_link,
     h7_reminder_subject, h7_reminder_body)
  VALUES
    ('Treking - Gunung Api Purba Nglanggeran - Sumber Template', 'private', 'cave', 'Ditutup',
     'Gunung Api Purba Nglanggeran', 'Nglanggeran Ancient Volcano',
     'Gunung Api Purba Nglanggeran merupakan destinasi trekking populer di Gunungkidul dengan ketinggian sekitar 700 mdpl. Jalur trekking membawa peserta melewati kawasan batuan vulkanik purba, hutan dan medan berbatu hingga mencapai puncak dengan panorama perbukitan Nglanggeran, hamparan persawahan, serta lanskap Gunungkidul dari ketinggian. Cocok untuk pengalaman trekking singkat dengan kombinasi petualangan dan pemandangan alam.',
     'Nglanggeran Ancient Volcano is a popular trekking destination in Gunungkidul, Yogyakarta, standing at around 700 meters above sea level. The trek takes you through ancient volcanic rock formations and scenic natural landscapes, leading to a viewpoint overlooking the surrounding hills, rice fields, and the beautiful Gunungkidul countryside. It is a great choice for a short and adventurous trek with rewarding panoramic views.',
     JSON_ARRAY(
       '06.30 - sampai di Meeting Point (Loket Gunung Api Purba Nglanggeran)',
       '07.00 - briefing dan pemanasan',
       '07.20 - mulai treking',
       '08.20 - sampai di Puncak Bagong, take video drone',
       '09.40 - sampai di puncak Gunung Api Purba',
       '10.30 - treking turun',
       '11.30 - selesai, sayonara'),
     JSON_ARRAY(
       '06:30 – Arrive at the Meeting Point (Nglanggeran Ancient Volcano Ticket Counter)',
       '07:00 – Briefing and warm-up',
       '07:20 – Start trekking',
       '08:20 – Arrive at Bagong Peak, drone video session',
       '09:40 – Reach the summit of Nglanggeran Ancient Volcano',
       '10:30 – Trek downhill',
       '11:30 – Finish, sayonara!'),
     JSON_ARRAY(
       'Pemandu (Guide)',
       'HTM Gunung Api Purba',
       'P3K standar',
       'Fasilitas pendopo & toilet',
       'Dokumentasi drone (1 orang 1 baterai)',
       'Snack'),
     JSON_ARRAY(
       'Professional Guide',
       'Nglanggeran Ancient Volcano Entrance Ticket',
       'Standard First Aid Kit',
       'Shelter & Toilet Facilities',
       'Drone Documentation',
       'Snacks'),
     275000, 3, 3, 1, 3, 3,
     NULL, NULL,
     'Tersedia setiap tanggal dalam bulan yang dipilih. Peserta 1–3 orang per booking; tanggal dan sesi yang sama dapat dipesan beberapa customer. Hadir pukul 06.30 WIB.',
     'Available every day of the selected month. 1–3 participants per booking; multiple customers may book the same date and session. Please arrive at 06:30 WIB.',
     1, 'shared', 1,
     'Reminder – Treking - Gunung Api Purba Nglanggeran | {tanggal_trip}', reminder_body);
  SET nglanggeran_id = LAST_INSERT_ID();

  INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES
    (nglanggeran_id, 1, 475000),
    (nglanggeran_id, 2, 325000),
    (nglanggeran_id, 3, 275000);

  INSERT INTO trip_sessions
    (trip_id, session_code, name, start_time, end_time, drive_link_url, status)
  VALUES
    (nglanggeran_id, 'PAGI', 'Sesi Pagi', '07:00:00', '11:30:00', NULL, 'active');

  -- Camera sekali per booking, kedua ojek per peserta, mobil per 3 peserta.
  -- Add-on tidak membutuhkan link; dokumentasi drone termasuk pada trip (Include Drive).
  INSERT INTO trip_addons
    (trip_id, name, price, max_participants_per_unit, worker_action, status, sort_order, created_at, updated_at)
  VALUES
    (nglanggeran_id, 'Camera Insta360 (unit camera, memori 32gb, monopod, 1 battery)', 250000, NULL, 'none', 'active', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (nglanggeran_id, 'Ojek Trip Trekking Only, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor)', 200000, 1, 'none', 'active', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (nglanggeran_id, 'Ojek Trip Fullday, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor, stay 10 jam)', 400000, 1, 'none', 'active', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (nglanggeran_id, 'Transportasi B - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 3 orang', 650000, 3, 'none', 'active', 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

  -- Media kosong. Link Drive dokumentasi bawaan diisi admin setelah kegiatan.
  INSERT INTO trip_generation_templates
    (template_key, source_trip_id, name, trip_type, pattern_label, schedule_pattern_json, status, sort_order)
  VALUES
    ('private-treking-gunung-api-purba-nglanggeran', nglanggeran_id,
     'Treking - Gunung Api Purba Nglanggeran', 'private',
     'Tersedia setiap tanggal dalam bulan terpilih; Sesi Pagi 07.00–11.30 WIB; bersama; 1–3 peserta per booking',
     NULL, 'active', 180);

  COMMIT;
  SELECT 'Template private Treking - Gunung Api Purba Nglanggeran berhasil dibuat.' AS result,
         nglanggeran_id AS source_trip_id;
END$$
CALL seed_private_nglanggeran_20261004()$$
DROP PROCEDURE seed_private_nglanggeran_20261004$$
DELIMITER ;
