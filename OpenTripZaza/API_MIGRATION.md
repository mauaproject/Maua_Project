# PHP + MariaDB API

## Konfigurasi Hostinger

1. Salin `.env.example` menjadi `.env` jika environment file dapat dibaca oleh PHP.
2. Isi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, dan `DB_PORT`.
3. Isi `VITE_API_BASE_URL` dengan URL publik folder API, misalnya `https://domain.com/api`.
4. Jika Hostinger tidak menyediakan environment variable, salin
   `api/config/database.local.example.php` menjadi `api/config/database.local.php` lalu isi kredensial di sana.
   File lokal tersebut sudah masuk `.gitignore`.
5. Pastikan folder `uploads/trips`, `uploads/payment-proofs`, dan `uploads/worker-proofs`
   dapat ditulis oleh PHP (umumnya permission `755`).

Jangan mengunggah `.env` atau `database.local.php` ke GitHub. Setelah kredensial pernah
dibagikan di chat atau tempat publik, rotasi password database melalui hPanel.

## Inisialisasi

- Seed add-on sekali: `POST /api/addons/seed.php`
- Seed admin dari `ADMIN_NAME`, `ADMIN_EMAIL`, dan `ADMIN_PASSWORD`:
  `POST /api/users/seed-admin.php`

Endpoint seed tidak akan menambah data jika tabel terkait sudah terisi.

## Migrasi add-on per trip

Jalankan file berikut satu kali melalui menu SQL phpMyAdmin:

`api/migrations/2026-06-18-trip-addons.sql`

Migrasi ini:

- membuat tabel `trip_addons`;
- menambahkan relasi add-on trip pada `booking_addons`;
- menyimpan nama dan aksi worker pada `worker_tasks`;
- tetap mempertahankan kolom add-on lama agar booking lama tidak langsung rusak.

Harga add-on adalah harga tetap per booking dan tidak dikalikan jumlah peserta.
Pilihan aksi worker:

- `drive_link`: worker wajib mengisi link hasil Google Drive;
- `none`: worker cukup mencentang pekerjaan selesai tanpa upload.

## Migrasi pembayaran checkout

Jalankan file berikut satu kali melalui phpMyAdmin:

`api/migrations/2026-06-19-booking-payments.sql`

Tambahkan konfigurasi rekening publik ke `.env` sebelum menjalankan build frontend:

```env
VITE_BCA_ACCOUNT_NUMBER=1234567890
VITE_BCA_ACCOUNT_NAME=MAUA PROJECT
```

Flow pembayaran menyimpan jenis pembayaran DP/Lunas, nominal yang dibayar,
status verifikasi, snapshot nomor rekening BCA, dan URL bukti pembayaran.
Bukti pembayaran disimpan di `uploads/payment-proofs`, bukan sebagai nama file lokal.
Jalankan `npm run build` kembali setiap kali nilai `VITE_...` frontend berubah.

## Migrasi Paket Private Trip

Jalankan file berikut secara berurutan melalui phpMyAdmin:

`api/migrations/2026-06-19-private-trip-packages.sql`

`api/migrations/2026-06-19-package-price-tiers.sql`

Migrasi ini membuat tabel paket serta tier harga paket yang terpisah dari sesi.
Harga lama pada `private_price_tiers` tidak dihapus dan tetap digunakan oleh
private trip tanpa paket. Setelah migrasi:

- private trip tanpa paket memakai `private_price_tiers`;
- private trip dengan paket memakai `package_price_tiers` milik paket terpilih;
- customer memilih satu paket jika paket tersedia dan tetap memilih satu sesi;
- subtotal trip dihitung dari harga per orang sesuai jumlah peserta dikali jumlah peserta;
- total harga adalah subtotal trip ditambah total add-on.

## Tes cepat

```bash
curl https://domain.com/api/trips/index.php
curl -X POST https://domain.com/api/addons/seed.php
curl -X POST https://domain.com/api/users/seed-admin.php
curl "https://domain.com/api/bookings/user.php?email=user@example.com"
```

Semua endpoint memberi bentuk respons:

```json
{"success":true,"data":[]}
```

atau:

```json
{"success":false,"message":"Pesan error"}
```

## Catatan deployment Vite

Jalankan `npm run build`, lalu unggah isi folder `dist` ke document root website.
Folder `api` dan `uploads` harus berada pada host yang dapat dicapai oleh
`VITE_API_BASE_URL`. Untuk development lokal, PHP harus dijalankan lewat Apache,
Nginx, atau `php -S`; Vite sendiri tidak mengeksekusi file PHP.

## Email reminder H-7, H-1, dan H+1

1. Jalankan migrasi `api/migrations/2026-06-19-email-reminders.sql` satu kali
   melalui phpMyAdmin.
2. Tambahkan konfigurasi berikut ke `.env` server:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_FROM_ADDRESS=email@gmail.com
MAIL_FROM_NAME=MAUA Project
ADMIN_WHATSAPP=62882005881248
REVIEW_URL=https://alamat-link-review
PAYMENT_DETAILS=Transfer ke rekening ... atas nama ...
CRON_SECRET=ganti_dengan_token_acak_panjang
APP_TIMEZONE=Asia/Jakarta
```

Untuk Gmail, gunakan **App Password**, bukan password login akun. App Password
memerlukan verifikasi 2 langkah pada akun Google.

Script cron:

`api/cron/send-reminders.php`

Contoh perintah cron Hostinger jika PHP CLI tersedia:

```bash
/usr/bin/php /home/USERNAME/domains/DOMAIN/public_html/api/cron/send-reminders.php
```

Jadwalkan satu kali setiap hari, misalnya pukul 08:00. Alternatif jika Hostinger
hanya menerima URL:

```text
https://DOMAIN/api/cron/send-reminders.php?token=ISI_CRON_SECRET
```

Gunakan URL HTTPS dan token acak yang panjang. Script akan:

- mengirim H-7, H-1 beserta PDF invoice, dan H+1;
- hanya memproses booking berstatus `Disetujui` atau `Selesai`;
- mencatat hasil ke tabel `reminder_logs` agar email sukses tidak terkirim ulang;
- mengarsipkan booking dan jadwal dari daftar aktif setelah `visible_until`;
- mempertahankan seluruh record database (tidak melakukan hard delete).

Booking arsip dapat diambil melalui:

- `GET /api/bookings/index.php?archived=1`
- `GET /api/bookings/user.php?email=user@example.com&archived=1`

## Verifikasi email customer

1. Jalankan migrasi `api/migrations/2026-06-19-email-verification.sql`
   melalui phpMyAdmin.
2. Jalankan migrasi `api/migrations/2026-06-19-email-verification-otp.sql`.
3. Pastikan konfigurasi SMTP pada `.env` sudah benar.

Alur signup menggunakan OTP:

1. Data signup disimpan sementara di `pending_customer_registrations`.
2. Sistem mengirim kode OTP 6 digit ke email customer.
3. OTP berlaku 30 menit dan maksimal lima percobaan.
4. Record pada tabel `users` baru dibuat setelah OTP benar.
5. Setelah akun dibuat, customer diarahkan ke halaman login.

- `POST /api/auth/register.php`
- `POST /api/auth/resend-verification.php`
- `POST /api/auth/verify-email.php`
- `GET /api/auth/me.php?email=...`

OTP disimpan sebagai password hash, bukan dalam bentuk angka asli. Customer yang
belum terverifikasi tetap akan ditolak oleh endpoint pembuatan booking meskipun
validasi frontend dilewati.

## Review Pengunjung

Jalankan migrasi berikut satu kali melalui phpMyAdmin:

`api/migrations/2026-06-19-reviews.sql`

Review baru langsung berstatus `approved`, tetapi hanya dapat dibuat untuk booking
milik user yang berstatus `Disetujui` atau `Selesai`. Satu booking hanya dapat
memiliki satu review. Review `hidden` dan `deleted` tidak dikirim ke halaman publik.
