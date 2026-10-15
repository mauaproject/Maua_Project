# Blog admin dan artikel publik

Fitur terpasang pada `/admin/blog`, `/blog`, dan `/blog/{slug}`. Rute admin memakai
login yang sudah ada. Penulisan, upload, membaca draft, dan perubahan status juga
memeriksa Bearer token serta peran admin di API.

## Aktivasi di hosting (sebelum push/deploy)

1. Backup database seperti biasa, lalu jalankan
   `api/migrations/2026-10-14-blog.sql` satu kali melalui phpMyAdmin. Aman dijalankan
   ulang. Migrasi hanya membuat `blog_posts`, `blog_media`, dan `blog_post_media`;
   tabel trip, booking, pembayaran, review, user, dan tim tidak diubah.
2. Pastikan deployment menyalin `api/blog/` dan folder `uploads/blog/`, termasuk
   `.htaccess` di dalamnya. Jangan mengganti atau menghapus isi `uploads/` ketika
   mengunggah build baru. PHP perlu izin tulis pada `uploads/blog/` (umumnya 755).
3. PHP membutuhkan PDO MySQL, DOM, mbstring, fileinfo, dan GD dengan WebP. Atur
   `upload_max_filesize` minimal 10M serta `post_max_size` lebih besar, misalnya 12M.
4. Jalankan `npm run build`. Deploy output `dist/` bersama API sesuai alur hosting
   yang sudah dipakai. `.htaccess`, renderer SEO, dan sitemap terbaru berada di
   `dist/`. Tidak ada push atau deploy otomatis yang dilakukan oleh pengerjaan ini.
5. Login admin, buka **Blog**, lalu buat artikel pertama. Database dimulai kosong:
   tidak ada seed artikel lama atau artikel contoh. File pratinjau terpisah tidak
   masuk ke build utama.

## Penyimpanan

- Judul, ringkasan, HTML yang sudah dibersihkan, kategori, penulis, bahasa ID/EN,
  pengaturan SEO, ukuran/posisi sampul, versi, dan status disimpan di `blog_posts`.
- Keyword disimpan terurut di `blog_post_keywords`, terpisah dari HTML tulisan.
- JPG/PNG/WebP maksimal 10 MB dan 16 megapiksel diunggah ke `api/blog/upload.php`.
  Server memeriksa isi file, mengubahnya menjadi WebP maksimal sisi 1600 px, dan
  membuat thumbnail maksimal 480 px. Nama file memakai ID acak.
- File berada di `uploads/blog/{id}.webp` dan `{id}-thumb.webp`, terpisah dari trip
  dan bukti pembayaran. Artikel menyimpan URL, bukan data base64/localStorage.
- `blog_media` mencatat file. `blog_post_media` mencatat semua gambar yang dipakai
  sampul maupun isi artikel, termasuk versi Inggris.
- Artikel di sampah dan gambar tetap disimpan agar dapat dipulihkan. Menghapus
  gambar dari isi artikel tidak menghapus file fisiknya. Gambar dari upload yang
  dibatalkan juga tetap tersimpan; tidak ada pembersihan otomatis yang berisiko
  menghapus gambar yang sedang dipakai. Backup folder blog bersama database.

## Perilaku

Draft tidak muncul pada API publik, SEO, maupun sitemap. Publish memerlukan judul,
ringkasan, isi, sampul, dan deskripsi gambar. Artikel dapat ditarik menjadi draft,
dipindahkan ke sampah, dan dipulihkan sebagai draft. Pembaruan memakai nomor versi
agar dua sesi admin tidak diam-diam saling menimpa. Bahasa Inggris bersifat opsional;
versi Indonesia dipakai jika terjemahannya belum lengkap.

Pencarian, kategori, dan pagination daftar publik berjalan di server. Tampilan
tetap memakai sidebar, navbar, font, dan tema aplikasi. Daftar isi dibentuk dari
H2/H3. Preview gambar pada dialog ukurannya tetap; perubahan ukuran diterapkan
pada artikel setelah admin menekan tombol konfirmasi.

## Keyword artikel

Untuk hosting yang sudah menggunakan blog, backup database lalu jalankan
`api/migrations/2026-10-15-blog-keywords.sql` **sebelum push/deploy** pembaruan ini.
Untuk instalasi baru, jalankan setelah migrasi blog 14 Oktober. Migrasi tambahan
hanya membuat tabel `blog_post_keywords`, dapat dijalankan ulang, dan tidak
menghapus atau mengubah artikel lama maupun tabel fitur lain.

Kolom **Keyword** berada di bawah bagian gambar sampul. Isi frasa dipisahkan
koma, misalnya `Wisata Goa Jomblang, Vertical Caving Jogja`. Opsional, maksimal
10 frasa dengan panjang masing-masing 80 karakter. Spasi berlebih dan keyword
duplikat dengan perbedaan kapital dirapikan saat disimpan. Daftar keyword berlaku
untuk satu artikel, termasuk ketika bahasa konten diganti.

Keyword ikut pencarian publik/admin, tampil sebagai tag bertaut pada halaman
artikel, dan dipakai untuk memilih artikel terkait dari seluruh artikel terbit.
Kesamaan keyword didahulukan, lalu kategori dan tanggal. Draft dan sampah tidak
masuk hasil publik. Tag, link artikel terkait, dan properti `keywords` pada
structured data Article/NewsArticle/BlogPosting juga dirender PHP agar tersedia
tanpa JavaScript. Keyword bukan meta-keywords dan tidak disisipkan otomatis ke
judul, deskripsi, teks alternatif gambar, atau isi tulisan.

Petunjuk editor hanya memeriksa apakah frasa persis sudah disebut di judul/isi
bahasa yang sedang diedit. Petunjuk tidak menjadi syarat publish atau skor SEO.
Pilih keyword yang sesuai dengan topik dan tulis secara alami. Penambahan kolom
atau properti schema tidak menjamin kenaikan peringkat Google. Rujukan:
[Google Search Essentials](https://developers.google.com/search/docs/essentials),
[Schema.org keywords](https://schema.org/keywords), dan
[meta tag yang didukung Google](https://developers.google.com/search/docs/crawling-indexing/special-tags).

Sebelum migrasi tambahan dijalankan, membaca dan menyimpan artikel tanpa keyword
tetap berfungsi; menyimpan keyword akan menampilkan pesan migrasi kepada admin.
Klien lama yang tidak mengirim properti keyword tidak menghapus keyword yang
sudah tersimpan. Mengosongkan kolom pada editor baru menghapus hanya keyword
artikel tersebut. Pindah sampah/pulihkan mempertahankan keyword.

## Pemeriksaan

`php api/tests/blog-content.php` memeriksa pembersihan HTML dan validasi konten.
`php api/tests/blog-integration.php` memeriksa alur HTTP dengan MySQL dan PHP server
uji terpisah; lihat instruksi di awal file. Jangan arahkan tes integrasi ke hosting
atau database produksi. Tes reschedule yang sudah ada tetap dapat dijalankan
dengan `php api/tests/reschedule-fee-reservation.php`.
