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

## Pemeriksaan

`php api/tests/blog-content.php` memeriksa pembersihan HTML dan validasi konten.
`php api/tests/blog-integration.php` memeriksa alur HTTP dengan MySQL dan PHP server
uji terpisah; lihat instruksi di awal file. Jangan arahkan tes integrasi ke hosting
atau database produksi. Tes reschedule yang sudah ada tetap dapat dijalankan
dengan `php api/tests/reschedule-fee-reservation.php`.
