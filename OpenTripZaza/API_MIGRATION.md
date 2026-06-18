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
