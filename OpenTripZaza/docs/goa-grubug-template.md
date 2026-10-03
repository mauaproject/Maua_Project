# Template private trip Goa Grubug

Status: materi dan migration siap. Pemasangan pada database website dilakukan dengan mengimpor `api/migrations/2026-10-02-private-goa-grubug-template.sql` melalui phpMyAdmin.

## Detail yang sudah disesuaikan

- Jam mulai caving dikonfirmasi **09.30 WIB**. Kedatangan paling lambat 09.00 dan registrasi 09.10; caving diperkirakan selesai 11.00–11.30, dilanjutkan bersih-bersih dan makan siang hingga kegiatan berakhir 13.00.
- Meeting point mengikuti koreksi terakhir: **Goa Jomblang**, https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic. Nama kegiatan tetap Goa Grubug.
- Judul bagian teknis reminder dikoreksi menjadi Goa Grubug agar konsisten dengan nama kegiatan.
- Angka durasi yang terpotong pada pesan ditafsirkan sebagai **1,5–2 jam**.
- Versi Indonesia dan Inggris menggunakan lokasi dan jadwal yang sama.

## Konfigurasi template

| Pengaturan | Nilai |
|---|---|
| Nama | Goa Grubug |
| Jenis | Private Trip |
| Kategori | Wisata Goa (`cave`) |
| Destinasi Indonesia | Goa Grubug |
| Destinasi Inggris | Grubug Cave |
| Dokumentasi termasuk link Google Drive | Tidak |
| Peserta minimum | 1 |
| Peserta maksimum | 40 |
| Kapasitas booking | Eksklusif: satu booking per tanggal dan sesi |
| Ketersediaan | Tanggal pertama hingga terakhir bulan yang dipilih saat Generate |
| Sesi | Sesi 1, 09.00–13.00 WIB |
| Media | Kosong |
| Add-on | Salin persis dari private Goa Jomblang |
| Harga 1 peserta | Rp3.000.000/orang |
| Harga 2 peserta | Rp1.500.000/orang |
| Harga 3–40 peserta | Rp1.000.000/orang |

Contoh subtotal trip: 1 peserta = Rp3.000.000; 2 peserta = Rp3.000.000; 3 peserta = Rp3.000.000; 4 peserta = Rp4.000.000; 40 peserta = Rp40.000.000. Add-on dihitung terpisah.

Sistem yang tersedia sudah mendukung tiga tingkatan harga, dengan harga tingkat terakhir dipakai untuk jumlah peserta lebih besar. Bulan/tahun tidak perlu dikunci di template; keduanya dipilih pada generator paket.

## Add-on pembanding

Referensi: salinan SQL lokal `u124793915_maua_project (9).sql`, private Goa Jomblang, ID 38. Ini bukan verifikasi data live. Ketika template dipasang, salin dari record private Goa Jomblang pada database target.

| Add-on | Harga pada salinan lokal | Aksi pekerja |
|---|---:|---|
| Dokumentasi Foto Camera + Video iPhone | Rp950.000 | Link Google Drive |
| Dokumentasi Foto Camera | Rp750.000 | Link Google Drive |
| Dokumentasi Foto + Video iPhone | Rp850.000 | Link Google Drive |
| Camera Insta360 | Rp250.000 | Selesai tanpa link |
| Transportasi A - Mobil maksimal 5 orang | Rp850.000 | Selesai tanpa link |
| Ojek Trip Jomblang Only | Rp200.000 | Selesai tanpa link |
| Ojek Trip Fullday | Rp400.000 | Selesai tanpa link |

Dokumentasi bawaan paket dinonaktifkan. Add-on dokumentasi tetap mempertahankan aksi pekerja milik Jomblang, karena diminta disalin persis.

## Deskripsi Indonesia

Goa Grubug menawarkan pengalaman turun ke dalam sinkhole alami sedalam sekitar 100 meter tepat di spot Heaven’s Light menggunakan sistem hauling. Setelah mencapai dasar goa, peserta akan diajak menikmati fenomena Cahaya Surga dari dalam goa, yaitu sinar matahari yang menembus mulut goa dan menciptakan pemandangan yang spektakuler. Trip ini cocok bagi pemula maupun pecinta petualangan yang ingin merasakan sensasi eksplorasi goa dengan tetap didampingi oleh tim profesional dan mengutamakan standar keselamatan.

## English description

Grubug Cave offers the experience of descending into a natural sinkhole approximately 100 meters deep, directly at the Heaven’s Light spot, using a hauling system. Upon reaching the cave floor, participants can enjoy the spectacular Heaven’s Light phenomenon, where sunlight streams through the cave opening and illuminates the space below. This trip is suitable for beginners and adventure enthusiasts who want to experience cave exploration with professional assistance and an emphasis on safety standards.

## Aktivitas Indonesia

- Vertical caving di spot Heaven’s Light, satu per satu.
- Eksplorasi goa horizontal.
- Pengambilan foto dan video di spot Light of Heaven.

## Activities — English

- Vertical caving at the Heaven’s Light spot, one participant at a time.
- Exploring the cave’s horizontal passages.
- Taking photos and videos at the Light of Heaven spot.

## Fasilitas Indonesia

- Pemandu bersertifikat.
- Perlengkapan keselamatan berstandar internasional: helm dan sepatu boots.
- Durasi caving sekitar 1,5–2 jam, dimulai pukul 09.30 WIB.
- Makan siang dan air mineral setelah kegiatan.
- Fasilitas pendopo dan toilet.

## Facilities — English

- Certified guide.
- International-standard safety equipment: helmet and boots.
- Approximately 1.5–2 hours of caving, starting at 09:30 WIB (UTC+7).
- Lunch and mineral water after the activity.
- Pavilion and toilet facilities.

## Reminder Indonesia

Subject:

Reminder – Reguler Caving Goa Grubug | {tanggal_trip}

Body:

Hi, Sobat Maua 👋
Terima kasih atas antusiasme dan kepercayaannya untuk mengikuti kegiatan Reguler Caving di Goa Grubug. Tidak terasa, kegiatan kita sudah memasuki H-{sisa_hari} pelaksanaan 😊🙏🏻

Untuk menunjang kenyamanan dan kelancaran kegiatan, berikut beberapa perlengkapan yang kami sarankan untuk dipersiapkan:

• Pakaian yang nyaman untuk aktivitas outdoor (hindari pakaian yang terlalu berat, memiliki tali panjang yang menjuntai, serta penggunaan rok atau gamis).
• Pakaian ganti dan perlengkapan mandi (medan saat ini cukup berlumpur).
• Jas hujan atau ponco.
• Hydropack/daypack/tas yang nyaman digunakan saat aktivitas (tidak disarankan menggunakan sling bag atau tas bahu karena dapat mengganggu pergerakan saat menggunakan tali).
• Peralatan dokumentasi pribadi apabila diperlukan.
• Obat-obatan pribadi bagi peserta yang memiliki kebutuhan atau riwayat kesehatan tertentu.

Teknis Kegiatan Reguler Caving Goa Grubug

📍 Meeting Point: Goa Jomblang (https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic)
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
Maua Project Team 🌿

## English reminder

Subject:

Reminder – Regular Caving at Grubug Cave | {tanggal_trip}

Body:

Hi, Maua friends 👋
Thank you for your enthusiasm and for choosing to join our Regular Caving experience at Grubug Cave. There are only {sisa_hari} days left until our adventure 😊🙏🏻

To help you prepare for a comfortable and smooth experience, we recommend bringing the following:

• Comfortable outdoor clothing. Avoid heavy clothing, long dangling straps, skirts, and long dresses.
• A change of clothes and toiletries, as the terrain is currently quite muddy.
• A raincoat or poncho.
• A hydration pack, daypack, or another comfortable backpack suitable for the activity. Sling bags and shoulder bags are not recommended because they may restrict movement when using ropes.
• Personal photography or video equipment, if needed.
• Personal medication if you have specific medical needs or a relevant medical history.

Regular Caving at Grubug Cave — Activity Details

📍 Meeting Point: Jomblang Cave (https://maps.app.goo.gl/McUYxTUKbeUUsoQ59?g_st=ic)
📆 Date: {tanggal_trip}
🕑 Time: {jam_trip}

📌 Activity Schedule

All times are in WIB (UTC+7).

07:00 – Guest pickup for participants who have booked the pickup service
09:00 – Latest arrival time at the meeting point
09:10 – Check-in
09:30 – Caving begins
11:00–11:30 – Estimated end of caving, followed by time to freshen up and have lunch
13:00 – Program ends. See you on the next adventure!

📢 Important Notes

• Get enough rest and avoid staying up late the night before the activity.
• We recommend having breakfast before joining the activity.
• Photography and video services cannot be added at the last minute on-site. Bookings must be confirmed no later than one day before the activity.
• Please arrive on time so the activity can run according to schedule.
• If you experience any difficulties or need further information, please contact the Maua Project team.

Warm regards,
Maua Project Team 🌿

## Catatan dukungan bahasa

Deskripsi, destinasi, aktivitas, dan fasilitas sudah memiliki kolom Indonesia–Inggris pada aplikasi.

Reminder saat ini memakai satu subject dan satu body per trip, bukan kolom terpisah per bahasa. Migration memasang reminder Indonesia; terjemahan Inggris reminder disiapkan di dokumen ini untuk dipilih atau dipakai kemudian. Sistem pengiriman email tidak diubah.

## Validasi lokal

Migration diuji pada MariaDB 10.4 terisolasi dengan schema trip dari proyek dan data add-on sintetis. Pengujian berhasil untuk pemasangan pertama, pengulangan tanpa duplikasi atau penimpaan, serta pembatalan transaksi ketika sumber Jomblang atau add-on tidak tersedia. Seluruh atribut add-on tersalin, termasuk batas peserta per unit, status, dan aksi worker.

Fungsi generator aplikasi berhasil membuat periode Oktober 2026 penuh dan Februari 2028 sampai tanggal 29, mempertahankan sesi, harga, konten dua bahasa, reminder, dan media kosong. Perhitungan harga backend dan fungsi harga frontend diperiksa untuk 1, 2, 3, 4, dan 40 peserta. Pengujian ini tidak memasang template pada database website.

