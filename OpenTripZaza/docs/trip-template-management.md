# Kelola Template Trip

Admin membuka **Paket Trip > Tambah Paket > Kelola Template**. Halaman ini
menampilkan nama, jenis Open/Private, dan ringkasan pola, serta menyediakan
pencarian, filter jenis, tambah, edit, dan hapus template.

Form tambah dan edit memakai komponen enam langkah yang sama dengan form paket.
Template tidak memiliki status aktif/nonaktif pada antarmuka. Semua template baru
langsung tersedia di generator. Private mencakup satu bulan penuh saat digunakan;
admin mengisi sesi dan tier harga tanpa memilih rentang tanggal pada template.

Open mendukung setiap hari, pilihan hari tiap pekan (termasuk pintasan weekend),
dan pilihan kemunculan hari ke-1 sampai ke-5 dalam bulan. Beberapa aturan dan sesi
dapat digabung dengan kuota masing-masing. Aturan yang menghasilkan hari dan jam
sesi yang sama ditolak. Kemunculan kelima dilewati jika tidak ada dalam bulan itu.
Preview tanggal bulanan tetap tersedia di modal pembuatan paket yang sudah ada.

Di langkah **Harga & jadwal** pada form template Open Trip, admin juga dapat
memilih bulan/tahun contoh lalu menekan **Lihat Preview** sebelum menyimpan.
Preview menampilkan tanggal, sesi, jam, kuota, dan ringkasan setiap aturan.
Kemunculan bulanan yang tidak tersedia dijelaskan per aturan; aturan lain tetap
dihitung. Pola atau periode contoh yang berubah menyembunyikan hasil lama sampai
preview diperbarui. Bulan/tahun contoh hanya disimpan pada tampilan form dan tidak
menjadi bagian dari template atau paket. Preview memakai pembuat jadwal yang
sama dengan generator dan tidak membuat record trip/template/generasi baru.

## Pemasangan

Setelah schema generator yang ada terpasang, jalankan sekali:

`api/migrations/2026-10-30-template-management.sql`

Migration aman diulang. Kolom `trips.is_template_source` memisahkan salinan sumber
baru dari daftar paket dan halaman detail publik. Relasi riwayat generasi berubah
menjadi `ON DELETE SET NULL`, sehingga hapus template mempertahankan riwayat yang
terhubung ke paket. Unggah frontend hasil build beserta endpoint PHP yang baru.
Pembuatan template selanjutnya dilakukan lewat dashboard tanpa SQL template.

## Pemisahan data

Setiap penyimpanan membuat sumber baru yang tertutup dan khusus untuk template.
Sumber sebelumnya dipertahankan; ini melindungi template lama yang mungkin
menunjuk langsung ke paket terbit. Endpoint tidak mengubah sumber lama, jadwal,
booking, atau paket yang sudah dibuat. Add-on dan data harga disalin ke sumber
baru. Penghapusan hanya menghapus record template, tanpa menghapus sumber atau
berkas foto. Foto yang dipakai paket tetap tersedia.

Save dan delete memeriksa versi sumber yang dibaca editor untuk mencegah tab lama
menimpa perubahan admin lain. Semua endpoint pengelolaan dan upload mewajibkan
sesi admin. Upload menggunakan ID template dan memeriksa sumber khusus template.

## Endpoint

- `GET /api/trip-templates/manage.php`: daftar template.
- `GET /api/trip-templates/manage.php?id=...`: detail form dan pola.
- `POST /api/trip-templates/save.php`: tambah/edit; ID sumber dari klien tidak
  pernah digunakan sebagai target update paket.
- `POST /api/trip-templates/preview.php`: hitung pola yang belum disimpan,
  termasuk keterangan urutan hari yang tidak tersedia. Memerlukan sesi admin.
- `POST /api/trip-templates/delete.php`: hapus termasuk template yang sudah dipakai.
- `POST /api/trip-templates/image.php`: foto sumber khusus template.

## Pengujian lokal

`node --test api/tests/trip-template-patterns.test.mjs` memeriksa konversi lima pola
SQL lama ke editor tanpa mengubah tanggal, sesi, atau kuota.

`api/tests/trip-template-management.php` dan `trip-template-router.php` memakai
MariaDB sementara di `127.0.0.1:33079` dan HTTP `127.0.0.1:8124`. Script setup hanya
membaca definisi schema dari dump, tidak memasukkan data pengguna atau booking
asli. Lihat komentar script untuk konfigurasi server. Database ini harus terpisah
dari database website. Pengujian membandingkan seluruh isi paket dan relasi
terkait sebelum/sesudah edit serta hapus, termasuk sumber template lama yang
merupakan paket terbit.

Validasi lokal 30 Oktober 2026: build produksi dan lint komponen baru lulus,
78 pemeriksaan integrasi pada MariaDB 10.4.32 lulus, dan empat pengujian pola
lulus. Pengujian HTTP pola lama diaktifkan dengan
`MAUA_TEMPLATE_PREVIEW_URL=http://127.0.0.1:8124` sebelum menjalankan pengujian Node.
Lima pola SQL lama diperiksa pada bulan dengan empat/lima kemunculan hari serta
Februari biasa dan kabisat. Foto yang disalin ke paket tetap ada sesudah foto dihapus dari template
atau template dihapus. Alur tambah/edit, konfirmasi hapus, form private satu
bulan, preview sebelum simpan, serta form paket biasa diperiksa melalui browser
pada preview lokal. Penambahan/penghapusan jadwal khusus saat membuat paket juga
diuji: jadwal paket berubah, sedangkan isi template dan paket sebelumnya tetap utuh.

## Unggah pembaruan

Sesudah migration pengelolaan template di atas berhasil dijalankan, preview ini
tidak memerlukan SQL tambahan. Unggah isi build frontend ke lokasi frontend
website dan seluruh endpoint `api/trip-templates/*.php`, beserta perubahan
`api/trips/index.php` dan `api/trips/detail.php`. Pertahankan konfigurasi database,
file environment, dan folder uploads milik website. Paket ZIP distribusi hanya
berisi build frontend serta file PHP tersebut, tanpa konfigurasi lokal, dump
database, data uji, atau unggahan pengguna.
