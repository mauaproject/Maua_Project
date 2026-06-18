<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    if ((int) $pdo->query('SELECT COUNT(*) FROM addons')->fetchColumn() > 0) {
        jsonSuccess(['seeded' => false, 'message' => 'Tabel addons sudah berisi data.']);
    }
    $addons = [
        ['drone', 'Drone', 'Operator drone', 'Dokumentasi aerial selama perjalanan dengan operator drone.', 'Mengoperasikan drone, mengambil footage aerial, menjaga area terbang tetap aman, dan menyerahkan hasil dokumentasi ke admin.'],
        ['documentation', 'Videografer/fotografer', 'Videografer/fotografer', 'Dokumentasi foto dan video aktivitas peserta selama trip.', 'Mendokumentasikan aktivitas peserta, mengarahkan momen foto/video, dan menyiapkan file dokumentasi perjalanan.'],
        ['camera360', 'Camera 360', 'Operator camera 360', 'Pengambilan konten 360 untuk momen utama perjalanan.', 'Mengoperasikan camera 360, mengatur antrean peserta saat pengambilan konten, dan memastikan hasil video tersimpan rapi.'],
        ['transport', 'Transportasi', 'Koordinator transportasi', 'Bantuan transportasi dari titik jemput yang diisi customer.', 'Menghubungi peserta, mengoordinasikan titik jemput, memastikan keberangkatan transportasi tepat waktu, dan melaporkan status perjalanan.'],
    ];
    $statement = $pdo->prepare('INSERT INTO addons (id, label, worker_title, description, task, status) VALUES (?,?,?,?,?, "active")');
    foreach ($addons as $addon) {
        $statement->execute($addon);
    }
    jsonSuccess(['seeded' => true, 'count' => count($addons)], 201);
});
