# Template Open Trip dan Private Trip Goa Sumelong

Migration: `api/migrations/2026-10-24-goa-sumelong-templates.sql`.

Status: sudah diuji lokal; belum dijalankan pada database website.

## Pemasangan

1. Pastikan generator `2026-08-31-trip-generation-templates.sql` dan schema trip terbaru sudah terpasang.
2. Di phpMyAdmin, pilih database MAUA lalu jalankan seluruh isi migration Goa Sumelong melalui tab SQL, sekali.
3. Setelah muncul **Template Goa Sumelong siap digunakan**, refresh admin.
4. Pilih **Paket Trip > Tambah Paket > Open Trip** atau **Private Trip**, pilih **Goa Sumelong**, lalu tentukan bulan dan tahun untuk membuat paket.

Migration menambahkan dua sumber berstatus **Ditutup** dan dua template aktif. Paket bulanan dibuat melalui generator. Foto sumber dan hasil generate kosong. Generator membaca database sehingga tidak diperlukan build frontend.

SQL dapat dijalankan ulang: template yang sudah terdaftar dilewati. Bila baru satu jenis yang ada, hanya jenis yang belum ada yang ditambahkan. Seluruh penambahan memakai transaksi dan dibatalkan bila terjadi galat. Data trip yang sudah ada tidak ditimpa.

## Konfigurasi

| Pengaturan | Open Trip | Private Trip |
|---|---|---|
| Template | Goa Sumelong | Goa Sumelong |
| Kunci | `open-goa-sumelong` | `private-goa-sumelong` |
| Destinasi ID / EN | Goa Sumelong / Sumelong Cave | Goa Sumelong / Sumelong Cave |
| Kategori | Wisata Goa (`cave`) | Wisata Goa (`cave`) |
| Jadwal | Setiap Minggu dalam bulan terpilih | Setiap tanggal dalam bulan terpilih |
| Sesi | Sesi Pagi, 08.00-13.00 WIB | Sesi Pagi, 08.00-13.00 WIB |
| Kapasitas | 5 peserta per sesi | 1-5 peserta per booking |
| Mode booking | Berbagi kuota sesi | Eksklusif, mengikuti mode private bawaan |
| Harga | Rp700.000/orang | Sesuai tabel berikut |
| Include Drive | Aktif, dokumentasi foto/video termasuk fasilitas | Aktif, dokumentasi foto/video termasuk fasilitas |
| Media | Kosong | Kosong |

Minimum 2 peserta memakai arti minimum biaya seperti klarifikasi paket sebelumnya. Pemesanan 1 orang tetap tersedia dengan tarif Rp1.400.000. Nilai teknis adalah `min_participants = 1`, `max_participants = 5`, dan `max_custom_pax = 2` untuk private. Harga tier ke-2 berlaku untuk 3-5 peserta.

| Peserta private | Harga per orang | Subtotal trip |
|---|---:|---:|
| 1 | Rp1.400.000 | Rp1.400.000 |
| 2 | Rp700.000 | Rp1.400.000 |
| 3 | Rp700.000 | Rp2.100.000 |
| 4 | Rp700.000 | Rp2.800.000 |
| 5 | Rp700.000 | Rp3.500.000 |

Biaya add-on terpisah. Kuota open trip sebesar 5 berlaku pada masing-masing sesi Minggu; kuota paket bulanan adalah jumlah kuota seluruh sesi.

## Add-on pada kedua jenis trip

| Add-on | Harga per unit | Kapasitas/perhitungan |
|---|---:|---|
| Camera Insta360, memori 32 GB, monopod, 1 baterai | Rp250.000 | Sekali per booking |
| Transportasi A, mobil + BBM + driver, Jogja Kota, stay 10 jam | Rp850.000 | 5 peserta/mobil |
| Transportasi B, mobil + BBM + driver, Jogja Kota, stay 10 jam | Rp650.000 | 3 peserta/mobil |
| Ojek Trip Goa Sumelong Only, BBM + driver + motor, Jogja Kota | Rp200.000 | 1 peserta/ojek |
| Ojek Trip Fullday, BBM + driver + motor, Jogja Kota, stay 10 jam | Rp400.000 | 1 peserta/ojek |

Untuk 5 peserta, Transportasi A dihitung 1 unit (Rp850.000), Transportasi B 2 unit (Rp1.300.000), ojek Sumelong 5 unit (Rp1.000.000), dan ojek fullday 5 unit (Rp2.000.000). Camera tetap sekali per booking. Semua add-on aktif dengan aksi worker `none`; dokumentasi bawaan dikelola melalui Include Drive.

## Rundown dan reminder

Deskripsi tersedia dalam dua bahasa. Rundown dan tujuh fasilitas disimpan dalam bahasa Indonesia dan Inggris. Pemilik secara eksplisit meminta rundown dipertahankan: caving **09.00-13.30**, bersih diri **11.30-12.30**, selesai **13.00**. Waktu tersebut masih bertumpang tindih; sesi booking tetap **08.00-13.00 WIB** sesuai konfirmasi.

Judul teknis pada reminder dikoreksi dari Goa Ngingrong menjadi **Goa Sumelong**. Nama **Goa Sumitro** pada meeting point, registrasi, lokasi fotografi, dan deskripsi dipertahankan sesuai materi pemilik.

Subject: **Reminder – Vertical Caving Goa Sumelong | {tanggal_trip}**.

Body mengikuti teks pemilik, termasuk format perlengkapan, emoji, placeholder `{tanggal_trip}`, `{jam_trip}`, `{sisa_hari}`, dan rundown. Meeting point adalah **Goa Sumitro**, https://maps.app.goo.gl/6v7puXz1YQapNS9R8?g_st=ipc.

## Validasi lokal

Migration lulus pada MariaDB 10.4 terisolasi dengan schema proyek dan data sintetis: pemasangan awal, pengulangan tanpa duplikasi, dan pemasangan ketika hanya salah satu jenis template sudah ada. Paket lama dengan nama Goa Sumelong beserta media, add-on, sesi, dan harganya tetap utuh. Galat yang dipicu saat membuat private membatalkan penambahan kedua jenis dalam transaksi.

Generator PHP asli menghasilkan seluruh Minggu dengan kuota 5 untuk 132 bulan pada 2020-2030. Enam paket bulanan untuk Oktober dan November 2026 serta Februari 2028 mempertahankan konten dua bahasa, lima add-on, tujuh fasilitas, reminder, dan foto kosong. Private tersedia sampai akhir bulan, termasuk 29 Februari 2028. Rundown Indonesia pada aktivitas dan reminder diperiksa persis terhadap materi yang dikonfirmasi, termasuk jam yang bertumpang tindih. Judul teknis menggunakan Sumelong, sementara meeting point tetap Sumitro. Placeholder tanggal/jam/sisa hari dan emoji berhasil dirender.

Fungsi harga frontend pada paket hasil generate lulus untuk 1-5 peserta, dengan subtotal Rp1.400.000, Rp1.400.000, Rp2.100.000, Rp2.800.000, dan Rp3.500.000. Unit dan total kelima add-on sesuai kapasitas camera/mobil/ojek. Pengujian tidak mengirim email atau memasang template pada website.
