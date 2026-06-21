-- Jalankan satu kali untuk memisahkan gambar katalog dan gambar detail.
-- image_url tetap menyimpan gambar detail agar data lama dan integrasi lama tetap kompatibel.

ALTER TABLE trip_images
    ADD COLUMN thumbnail_url VARCHAR(500) NULL AFTER image_url;
