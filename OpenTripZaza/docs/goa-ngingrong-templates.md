# Template Open Trip dan Private Trip Goa Ngingrong

Migration: `api/migrations/2026-10-24-goa-ngingrong-templates.sql`.

Status: migration sudah diuji lokal dan belum diimpor ke database website.

## Pemasangan

1. Pastikan migration generator `2026-08-31-trip-generation-templates.sql` dan schema trip terbaru sudah terpasang.
2. Buka database website di phpMyAdmin, lalu jalankan seluruh isi migration Goa Ngingrong melalui tab SQL.
3. Di admin, pilih **Paket Trip > Tambah Paket > Open Trip** atau **Private Trip**, pilih **Goa Ngingrong**, lalu pilih bulan dan tahun.
4. Periksa preview dan buat paket. Foto sumber dan hasil generate tetap kosong hingga admin mengisinya.

Generator membaca template dari database sehingga pemasangan ini tidak memerlukan build frontend. Migration membuat dua sumber khusus berstatus **Ditutup** dan dua template aktif. Paket yang sudah ada tetap utuh. Pengulangan SQL melewati template yang sudah terdaftar, termasuk bila baru salah satu jenis yang terpasang. Jika terjadi galat, penambahan kedua jenis dibatalkan dalam transaksi.

## Konfigurasi

| Pengaturan | Open Trip | Private Trip |
|---|---|---|
| Nama template | Goa Ngingrong | Goa Ngingrong |
| Kunci | `open-goa-ngingrong` | `private-goa-ngingrong` |
| Destinasi ID / EN | Goa Ngingrong / Ngingrong Cave | Goa Ngingrong / Ngingrong Cave |
| Kategori | Wisata Goa (`cave`) | Wisata Goa (`cave`) |
| Ketersediaan | Setiap hari Minggu dalam bulan terpilih | Setiap tanggal dalam bulan terpilih |
| Sesi | Sesi Pagi, 08.00-13.00 WIB | Sesi Pagi, 08.00-13.00 WIB |
| Kapasitas | 6 peserta per sesi | 1-6 peserta per booking |
| Mode booking | Berbagi kuota sesi | Eksklusif, mengikuti mode private bawaan |
| Harga | Rp650.000/orang | Mengikuti tabel di bawah |
| Include Drive | Aktif, dokumentasi termasuk fasilitas | Aktif, dokumentasi termasuk fasilitas |
| Media | Kosong | Kosong |

Klarifikasi pemilik: **minimum 3 peserta berarti minimum biaya senilai 3 peserta**. Pemesanan 1-2 peserta tetap diperbolehkan, sehingga `min_participants = 1`, `max_participants = 6`, dan `max_custom_pax = 3`. Sistem memakai harga tier ke-3 untuk 4-6 peserta.

| Peserta private | Harga per orang | Subtotal trip |
|---|---:|---:|
| 1 | Rp1.950.000 | Rp1.950.000 |
| 2 | Rp975.000 | Rp1.950.000 |
| 3 | Rp650.000 | Rp1.950.000 |
| 4 | Rp650.000 | Rp2.600.000 |
| 5 | Rp650.000 | Rp3.250.000 |
| 6 | Rp650.000 | Rp3.900.000 |

Biaya add-on dihitung terpisah. Untuk open trip, batas 6 berlaku pada masing-masing sesi Minggu; kuota paket bulanan merupakan jumlah seluruh sesi.

## Add-on pada kedua jenis trip

| Add-on | Harga per unit | Perhitungan |
|---|---:|---|
| Camera Insta360, memori 32 GB, monopod, 1 baterai | Rp250.000 | Sekali per booking |
| Baterai drone tambahan, waktu terbang 20 menit | Rp150.000 | Sekali per booking |
| Transportasi A, mobil + BBM + driver, Jogja Kota, stay 10 jam | Rp850.000 | 5 peserta/mobil |
| Transportasi B, mobil + BBM + driver, Jogja Kota, stay 10 jam | Rp650.000 | 3 peserta/mobil |
| Ojek Trip Goa Ngingrong Only, BBM + driver + motor, Jogja Kota | Rp200.000 | 1 peserta/ojek |
| Ojek Trip Fullday, BBM + driver + motor, Jogja Kota, stay 10 jam | Rp400.000 | 1 peserta/ojek |

Untuk 6 peserta, Transportasi A dihitung 2 unit atau Rp1.700.000; Transportasi B dihitung 2 unit atau Rp1.300.000. Ojek Ngingrong dihitung 6 unit atau Rp1.200.000 dan ojek fullday Rp2.400.000. Camera dan baterai tetap satu unit per booking. Semua add-on aktif dan memakai aksi worker `none`; link dokumentasi bawaan dikelola melalui Include Drive.

## Konten

Deskripsi Indonesia dan Inggris mengikuti materi pemilik. Rundown disimpan sebagai aktivitas dalam dua bahasa, termasuk persiapan pukul 08.00, descending 70 m, eksplorasi horizontal 200 m, descending 20 m, fotografi telaga pertama, kembali ke meeting point, dan makan siang mulai pukul 12.00. Sesi berakhir pukul 13.00 WIB.

Kesembilan fasilitas tersedia dalam dua bahasa. Reminder yang sama dipasang pada kedua sumber dengan subject **Reminder – Vertical Caving Goa Ngingrong | {tanggal_trip}** dan placeholder `{tanggal_trip}`, `{jam_trip}`, `{sisa_hari}`. Dua perlengkapan yang sebelumnya tergabung pada satu baris dipisahkan menjadi pakaian ganti serta hydropack/drypack.

Meeting point: **Geosite Ngingrong**, https://maps.app.goo.gl/wTu9Mp7s1UEuMCb7A?g_st=ic.

## Validasi lokal

Migration lulus pengujian pada MariaDB 10.4 terisolasi dengan schema proyek dan data sintetis: pemasangan awal, pengulangan tanpa perubahan, serta pemasangan ketika salah satu template sudah ada. Paket lama dengan nama destinasi yang sama beserta sesi, harga, add-on, dan media tetap utuh. Galat yang sengaja dipicu saat membuat private membatalkan penambahan kedua jenis trip dalam transaksi.

Generator PHP asli menghasilkan semua hari Minggu untuk 132 bulan pada 2020-2030. Enam paket hasil generate untuk Oktober dan November 2026 serta Februari 2028 mempertahankan sesi, konten dua bahasa, reminder, add-on, dan media kosong. Private mencakup setiap tanggal hingga akhir bulan, termasuk 29 Februari 2028. Preview mengenali paket yang sudah di-generate.

Fungsi harga frontend pada data hasil generate lulus untuk 1-6 peserta, termasuk subtotal minimum Rp1.950.000. Fungsi unit dan total keenam add-on lulus untuk jumlah peserta yang sama. Placeholder reminder, link meeting point, dan emoji tetap benar. Pengujian tidak mengirim email atau memasang data pada website.
