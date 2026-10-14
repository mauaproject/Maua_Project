# Template private Treking - Gunung Api Purba Nglanggeran

Materi dari client, 4 Oktober 2026. Jam akhir sesi dikonfirmasi 11.30 WIB; jumlah peserta dibatasi 1–3 orang per booking. Kedua add-on ojek dihitung per peserta.

## Pemasangan

Buka `api/migrations/2026-10-04-private-nglanggeran-template.sql`, salin **seluruh isinya**, lalu jalankan sekali di tab **SQL** phpMyAdmin pada database website. Generator paket bulanan (`2026-08-31-trip-generation-templates.sql`) dan schema trip terbaru harus sudah terpasang. Tidak perlu build atau upload ulang frontend.

Setelah berhasil, buka **Tambah Paket → Private Trip**, pilih **Treking - Gunung Api Purba Nglanggeran**, pilih bulan dan tahun, lalu **Generate**. Nama hasilnya **Treking - Gunung Api Purba Nglanggeran - [Bulan Tahun]**. Harga, sesi, dua bahasa, Include Drive, reminder, dan kapasitas add-on ikut ke setiap paket baru.

Migration membuat sumber tersendiri berstatus Ditutup; paket lama tidak diubah. Menjalankan SQL lagi setelah berhasil tidak membuat duplikasi atau menimpa perubahan admin. Jika terjadi galat sebelum transaksi selesai, penambahan data dibatalkan.

## Konfigurasi

| Pengaturan | Nilai |
|---|---|
| Nama template | Treking - Gunung Api Purba Nglanggeran |
| Key template | `private-treking-gunung-api-purba-nglanggeran` |
| Trip sumber | Treking - Gunung Api Purba Nglanggeran - Sumber Template |
| Status sumber | Ditutup |
| Jenis | Private Trip |
| Kategori | Wisata Goa (`cave`), sesuai permintaan client |
| Destinasi Indonesia | Gunung Api Purba Nglanggeran |
| Destinasi Inggris | Nglanggeran Ancient Volcano |
| Dokumentasi bawaan | Include Drive (`include_drive_link = 1`) |
| Aturan kapasitas booking | Bersama (`shared`) |
| Minimal / maksimal peserta | 1 / 3 per booking |
| Sesi | Sesi Pagi, 07.00–11.30 WIB |
| Kedatangan | 06.30 WIB di Loket Gunung Api Purba Nglanggeran |
| Periode | Seluruh tanggal pada bulan dan tahun yang dipilih saat Generate |
| Media | Kosong; diisi pada paket setelah generate |

Mode Bersama mengikuti perilaku aplikasi: beberapa customer dapat memesan tanggal dan sesi yang sama. Batas 3 peserta adalah batas per booking, bukan kuota gabungan seluruh booking pada tanggal/sesi tersebut.

Include Drive mengaktifkan dokumentasi bawaan. Link Drive diisi kemudian melalui admin; link dokumentasi sesi tidak disalin ke paket bulan baru.

## Harga

| Jumlah peserta | Harga per orang | Subtotal trip |
|---|---:|---:|
| 1 | Rp475.000 | Rp475.000 |
| 2 | Rp325.000 | Rp650.000 |
| 3 | Rp275.000 | Rp825.000 |

Biaya add-on terpisah. Tidak ada paket untuk lebih dari 3 peserta.

## Add-on

| Nama | Harga per unit | Kapasitas per unit |
|---|---:|---|
| Camera Insta360 (unit camera, memori 32gb, monopod, 1 battery) | Rp250.000 | Sekali per booking |
| Ojek Trip Trekking Only, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor) | Rp200.000 | 1 peserta/ojek |
| Ojek Trip Fullday, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Motor, stay 10 jam) | Rp400.000 | 1 peserta/ojek |
| Transportasi B - Sewa Mobil, Penjemputan & Penghantaran Jogja Kota Area (BBM+Driver+Mobil, stay 10 Jam) kapasitas maksimal 3 orang | Rp650.000 | 3 peserta/mobil |

Nama mobil mengikuti klarifikasi terbaru bahwa peserta tidak boleh lebih dari 3; frasa “atau lebih sesuai kesepakatan” dihapus. Nama yang disimpan muat dalam batas 150 karakter database. Semua add-on berstatus aktif dan memakai aksi worker `none` (selesai tanpa link); dokumentasi drone sudah termasuk pada trip.

Contoh: untuk 2 peserta, Ojek Trip Trekking Only dihitung 2 × Rp200.000 = Rp400.000; total bersama subtotal trip Rp650.000 menjadi Rp1.050.000. Untuk 3 peserta, Ojek Trip Fullday dihitung 3 × Rp400.000 = Rp1.200.000. Mobil tetap 1 × Rp650.000 untuk 1–3 peserta.

## Deskripsi Indonesia

Gunung Api Purba Nglanggeran merupakan destinasi trekking populer di Gunungkidul dengan ketinggian sekitar 700 mdpl. Jalur trekking membawa peserta melewati kawasan batuan vulkanik purba, hutan dan medan berbatu hingga mencapai puncak dengan panorama perbukitan Nglanggeran, hamparan persawahan, serta lanskap Gunungkidul dari ketinggian. Cocok untuk pengalaman trekking singkat dengan kombinasi petualangan dan pemandangan alam.

## English description

Nglanggeran Ancient Volcano is a popular trekking destination in Gunungkidul, Yogyakarta, standing at around 700 meters above sea level. The trek takes you through ancient volcanic rock formations and scenic natural landscapes, leading to a viewpoint overlooking the surrounding hills, rice fields, and the beautiful Gunungkidul countryside. It is a great choice for a short and adventurous trek with rewarding panoramic views.

## Aktivitas Indonesia

- 06.30 - sampai di Meeting Point (Loket Gunung Api Purba Nglanggeran)
- 07.00 - briefing dan pemanasan
- 07.20 - mulai treking
- 08.20 - sampai di Puncak Bagong, take video drone
- 09.40 - sampai di puncak Gunung Api Purba
- 10.30 - treking turun
- 11.30 - selesai, sayonara

## Activities — English

- 06:30 – Arrive at the Meeting Point (Nglanggeran Ancient Volcano Ticket Counter)
- 07:00 – Briefing and warm-up
- 07:20 – Start trekking
- 08:20 – Arrive at Bagong Peak, drone video session
- 09:40 – Reach the summit of Nglanggeran Ancient Volcano
- 10:30 – Trek downhill
- 11:30 – Finish, sayonara!

## Fasilitas Indonesia

- Pemandu (Guide)
- HTM Gunung Api Purba
- P3K standar
- Fasilitas pendopo & toilet
- Dokumentasi drone (1 orang 1 baterai)
- Snack

## Facilities — English

- Professional Guide
- Nglanggeran Ancient Volcano Entrance Ticket
- Standard First Aid Kit
- Shelter & Toilet Facilities
- Drone Documentation
- Snacks

## Reminder

Subject:

Reminder – Treking - Gunung Api Purba Nglanggeran | {tanggal_trip}

Body:

Hi, Sobat Maua 👋
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
Maua Project Team 🌿

## Validasi lokal

SQL diuji pada MariaDB 10.4 terisolasi menggunakan schema proyek dan data sintetis. Pemasangan berhasil saat sudah ada paket Nglanggeran bernama sama; paket lama, sesi, tier harga, add-on, dan gambarnya tidak berubah. Pengulangan SQL tidak menambah duplikasi atau menimpa data. Galat insert yang sengaja dipicu membatalkan seluruh penambahan trip, tier, dan sesi dalam transaksi.

Fungsi generator PHP asli menghasilkan Oktober 2026 penuh serta Februari 2028 hingga tanggal 29. Keduanya mempertahankan konten dua bahasa, reminder, tiga tier harga, batas peserta 1–3, mode Bersama, Include Drive, jam 07.00–11.30, dan kapasitas keempat add-on. Media tetap kosong dan link Drive lama dari sumber tidak disalin. Render reminder asli berhasil mengganti placeholder tanggal/jam/sisa hari, dengan jam 07:00–11:30 WIB dan link meeting point yang sesuai.

Fungsi harga dan add-on frontend diuji memakai data hasil generate untuk 1, 2, dan 3 peserta. Subtotal trip sesuai Rp475.000 / Rp650.000 / Rp825.000; kedua ojek dikalikan peserta, camera tetap sekali, dan mobil tetap satu unit. Pemeriksaan ini tidak memasang SQL pada database website dan tidak mengirim reminder.
