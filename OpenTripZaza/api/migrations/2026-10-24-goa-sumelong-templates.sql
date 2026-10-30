-- Template Open Trip dan Private Trip Goa Sumelong.
-- Prasyarat: 2026-08-31-trip-generation-templates.sql dan schema trip terbaru.
-- Jalankan seluruh file melalui tab SQL phpMyAdmin pada database website.
-- Open: setiap Minggu, Sesi Pagi 08.00-13.00 WIB, kuota 5 per sesi.
-- Private: setiap tanggal, 1-5 peserta; minimum biaya senilai 2 peserta.
-- Rundown mengikuti konfirmasi pemilik, termasuk jam yang bertumpang tindih.
-- Foto sumber dan hasil generate kosong; pengulangan melewati template yang sudah ada.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELIMITER $$
DROP PROCEDURE IF EXISTS seed_sumelong_templates_20261024$$
CREATE PROCEDURE seed_sumelong_templates_20261024()
BEGIN
  DECLARE kind_index INT DEFAULT 0;
  DECLARE trip_kind VARCHAR(7);
  DECLARE template_key_value VARCHAR(80);
  DECLARE existing_template_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE sumelong_source_id BIGINT UNSIGNED DEFAULT NULL;
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

  SET description_id_text = 'Goa Sumelong merupakan destinasi vertical caving yang berada di kawasan Pegunungan Karst Menoreh, Kulon Progo. Goa ini memiliki kedalaman vertikal sekitar 35 meter yang mana akan tembus ke dalam Goa Sumitro, atau biasa kita sebut goa ini sebagai Goa Sumitro versi panjang. Setelah memasuki goa, peserta akan diajak mengeksplorasi lorong-lorong horizontal yang dihiasi berbagai formasi batuan karst yang masih alami. Keistimewaan Goa Sumelong adalah memiliki pengalaman lengkap dengan adanya sungai bawah tanah yang mengalir di dalam goa, menciptakan suasana petualangan yang menantang dan berkesan bagi para pecinta alam maupun pemula yang ingin mencoba caving.';
  SET description_en_text = 'Sumelong Cave is a vertical caving destination located in the Menoreh Karst Mountains, Kulon Progo, Yogyakarta. The cave features a vertical descent of approximately 35 meters, leading directly into Sumitro Cave, often referred to as the longer version of Sumitro Cave.

Once inside, participants will explore horizontal passages adorned with stunning, well-preserved karst formations. What makes Sumelong Cave truly unique is its complete caving experience, featuring an underground river that flows through the cave, creating an exciting and unforgettable adventure for both nature enthusiasts and first-time cavers.';

  -- Jam rundown dipertahankan sesuai materi dan konfirmasi pemilik.
  SET activities_id_json = JSON_ARRAY(
    '08.00 - 08.30 registrasi peserta di Pintu Masuk Goa Sumitro',
    '08.30 - 09.00 persiapan Caving',
    '09.00 - 13.30 Caving, descending 35meter, menyusuri lorong fosil 300m, menyusuri lorong vedos 200m, fotografi di goa Sumitro',
    '11.30 - 12.30 bersih diri, minum air hangat & jajanan tradisional',
    '13.00 Selesai');
  SET activities_en_json = JSON_ARRAY(
    '08:00 - 08:30 Participant registration at the Sumitro Cave entrance',
    '08:30 - 09:00 Caving preparation',
    '09:00 - 13:30 Caving, descend 35 m, explore 300 m of fossil passages and 200 m of vadose passages, and take photos in Sumitro Cave',
    '11:30 - 12:30 Clean up, enjoy warm water and traditional snacks',
    '13:00 Finish');
  SET facilities_id_json = JSON_ARRAY(
    'Tiket Parkir, Wisata & Asuransi',
    'Alat Caving (Coverall/wearpack, Sepatu Boot, Helm, Harnes, Carabiner, Tali Kernmantel, Headlamp, Pelampung)',
    'Snack tradisional dan teh hangat',
    'P3K Standart',
    'Dokumentasi Program (Foto & video)',
    'Penitipan Barang',
    'Caving Guide');
  SET facilities_en_json = JSON_ARRAY(
    'Parking, admission, and insurance',
    'Caving equipment (coveralls, rubber boots, helmet, harness, carabiner, kernmantle rope, headlamp, and life jacket)',
    'Traditional snacks and warm tea',
    'Standard first aid kit',
    'Activity documentation (photos and video)',
    'Luggage storage',
    'Caving guide');

  -- Lima aturan mencakup setiap Minggu, termasuk Minggu ke-5 bila ada.
  SET open_schedule_pattern = '{"codePrefix":"GOA_SUMELONG","rules":[
    {"type":"nth_weekday","weekday":0,"nth":1,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":5}]},
    {"type":"nth_weekday","weekday":0,"nth":2,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":5}]},
    {"type":"nth_weekday","weekday":0,"nth":3,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":5}]},
    {"type":"nth_weekday","weekday":0,"nth":4,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":5}]},
    {"type":"nth_weekday","weekday":0,"nth":5,"sessions":[{"code":"PAGI","name":"Sesi Pagi","startTime":"08:00","endTime":"13:00","quota":5}]}
  ]}';

  SET reminder_body = 'Hi, Sobat Maua 👋
Terima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Vertical Caving Goa Sumelong. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻

Untuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:
- Pakaian ganti (untuk antisipasi kondisi basah)
- Hydropack atau drypack bagi yang ingin membawa barang pribadi (tidak disarankan menggunakan sling bag atau tas bahu agar tidak mengganggu aktivitas dan penggunaan tali)
- Sunscreen, topi, dan kacamata untuk melindungi diri dari paparan matahari
* Air minum tambahan, disarankan menggunakan tumbler untuk mengurangi sampah plastik
* Peralatan dokumentasi pribadi apabila diperlukan
* Obat-obatan pribadi bagi peserta yang memiliki riwayat penyakit tertentu

Teknis Kegiatan Vertical Caving Goa Sumelong
📍Meeting Point: Goa Sumitro (https://maps.app.goo.gl/6v7puXz1YQapNS9R8?g_st=ipc)
📆 Tanggal: {tanggal_trip}
🕑 Waktu: {jam_trip}

📌 Rundown Kegiatan
08.00 - 08.30 registrasi peserta di Pintu Masuk Goa Sumitro
08.30 - 09.00 persiapan Caving
09.00 - 13.30 Caving, descending 35meter, menyusuri lorong fosil 300m, menyusuri lorong vedos 200m, fotografi di goa Sumitro
11.30 - 12.30 bersih diri, minum air hangat & jajanan tradisional
13.00 Selesai

📢 Catatan Penting
•	Pastikan beristirahat yang cukup dan tidak begadang pada malam sebelum kegiatan.
•	Disarankan untuk sarapan terlebih dahulu sebelum mengikuti kegiatan.
•	Penambahan layanan dokumentasi tidak dapat dilakukan secara mendadak di lokasi. Pemesanan wajib dikonfirmasi paling lambat H-1 sebelum kegiatan.
•	Mohon hadir tepat waktu agar kegiatan dapat berjalan sesuai jadwal.
•	Apabila mengalami kendala atau membutuhkan informasi tambahan, silakan menghubungi tim Maua Project.
Salam hangat,
Maua Project Team 🌿';

  WHILE kind_index < 2 DO
    SET trip_kind = IF(kind_index = 0, 'open', 'private');
    SET template_key_value = CONCAT(trip_kind, '-goa-sumelong');
    SET existing_template_id = (
      SELECT id FROM trip_generation_templates
      WHERE template_key = template_key_value
      LIMIT 1
    );

    IF existing_template_id IS NULL THEN
      -- Minimum peserta teknis 1: minimum 2 berarti minimum biaya Rp1.400.000.
      INSERT INTO trips
        (name, trip_type, experience_type, status, destination_id, destination_en,
         description_id, description_en, activities_id, activities_en, facilities_id, facilities_en,
         price, quota, slots, min_participants, max_participants, max_custom_pax,
         available_start_date, available_end_date, private_notes, private_notes_en,
         flexible_schedule, private_booking_mode, include_drive_link,
         h7_reminder_subject, h7_reminder_body)
      VALUES
        (CONCAT('Goa Sumelong - Sumber Template ', IF(trip_kind = 'private', 'Private Trip', 'Open Trip')),
         trip_kind, 'cave', 'Ditutup', 'Goa Sumelong', 'Sumelong Cave',
         description_id_text, description_en_text, activities_id_json, activities_en_json,
         facilities_id_json, facilities_en_json,
         700000, 5, 5, 1, 5, IF(trip_kind = 'private', 2, 0),
         NULL, NULL,
         IF(trip_kind = 'private', 'Tersedia setiap tanggal dalam bulan yang dipilih. Satu booking eksklusif per tanggal dan sesi. Minimum biaya senilai 2 peserta (Rp1.400.000); 1 peserta tetap dapat memesan. Harga 2-5 peserta Rp700.000 per orang.', NULL),
         IF(trip_kind = 'private', 'Available every day of the selected month. One exclusive booking per date and session. Minimum charge is equivalent to 2 participants (IDR 1,400,000); bookings for 1 participant are welcome. For 2-5 participants, the price is IDR 700,000 per person.', NULL),
         IF(trip_kind = 'private', 1, 0), 'exclusive', 1,
         'Reminder – Vertical Caving Goa Sumelong | {tanggal_trip}', reminder_body);
      SET sumelong_source_id = LAST_INSERT_ID();

      IF trip_kind = 'private' THEN
        INSERT INTO private_price_tiers (trip_id, pax_count, price_per_person) VALUES
          (sumelong_source_id, 1, 1400000),
          (sumelong_source_id, 2, 700000);

        INSERT INTO trip_sessions
          (trip_id, session_code, name, start_time, end_time, drive_link_url, status)
        VALUES
          (sumelong_source_id, 'PAGI', 'Sesi Pagi', '08:00:00', '13:00:00', NULL, 'active');
      END IF;

      -- Camera sekali per booking; mobil per kapasitas; kedua ojek per peserta.
      -- Dokumentasi termasuk fasilitas (Include Drive); add-on selesai tanpa link.
      INSERT INTO trip_addons
        (trip_id, name, price, max_participants_per_unit, worker_action, status, sort_order, created_at, updated_at)
      VALUES
        (sumelong_source_id, 'Camera Insta360 (unit camera, memori 32gb, monopod, 1 battery)', 250000, NULL, 'none', 'active', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (sumelong_source_id, 'Transportasi A - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 5 orang', 850000, 5, 'none', 'active', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (sumelong_source_id, 'Transportasi B - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 3 orang', 650000, 3, 'none', 'active', 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (sumelong_source_id, 'Ojek Trip Goa Sumelong Only, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor)', 200000, 1, 'none', 'active', 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (sumelong_source_id, 'Ojek Trip Fullday, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor, stay 10 jam)', 400000, 1, 'none', 'active', 4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

      INSERT INTO trip_generation_templates
        (template_key, source_trip_id, name, trip_type, pattern_label, schedule_pattern_json, status, sort_order)
      VALUES
        (template_key_value, sumelong_source_id, 'Goa Sumelong', trip_kind,
         IF(trip_kind = 'private',
            'Tersedia setiap tanggal dalam bulan terpilih; Sesi Pagi 08.00-13.00 WIB; eksklusif; 1-5 peserta, minimum biaya senilai 2 peserta',
            'Setiap Minggu; Sesi Pagi 08.00-13.00 WIB; kuota 5 peserta per sesi'),
         IF(trip_kind = 'private', NULL, open_schedule_pattern), 'active', IF(trip_kind = 'private', 200, 70));
    END IF;

    SET kind_index = kind_index + 1;
  END WHILE;

  COMMIT;
  SELECT 'Template Goa Sumelong siap digunakan.' AS result, template_key, source_trip_id
  FROM trip_generation_templates
  WHERE template_key IN ('open-goa-sumelong', 'private-goa-sumelong')
  ORDER BY trip_type;
END$$
CALL seed_sumelong_templates_20261024()$$
DROP PROCEDURE seed_sumelong_templates_20261024$$
DELIMITER ;
