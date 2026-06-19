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
