<?php
declare(strict_types=1);
// CLI only. Dedicated disposable MySQL on 127.0.0.1:33077, database maua_blog_test.
// 1. php api/tests/blog-integration.php --setup
// 2. Start PHP on 127.0.0.1:8123 with DB_NAME=maua_blog_test, DB_PORT=33077,
//    DB_HOST=127.0.0.1, DB_USER=root, DB_PASSWORD empty, and APP_TIMEZONE=Asia/Jakarta.
//    Command: php -S 127.0.0.1:8123 api/tests/blog-router.php (after npm run build).
// 3. php api/tests/blog-integration.php
// Never point this test server at the production database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$pdo = new PDO('mysql:host=127.0.0.1;port=33077;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec('CREATE DATABASE IF NOT EXISTS maua_blog_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE maua_blog_test');
$root = dirname(__DIR__, 2);
$pdo->exec("CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED PRIMARY KEY, name VARCHAR(100), email VARCHAR(190), role VARCHAR(20), password_hash VARCHAR(255), email_verified TINYINT DEFAULT 1)");
$pdo->exec('CREATE TABLE IF NOT EXISTS user_sessions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED,token_hash CHAR(64),expires_at DATETIME,last_used_at DATETIME)');
$migration = (string) file_get_contents(dirname(__DIR__) . '/migrations/2026-10-14-blog.sql');
$pdo->exec($migration);
$pdo->exec($migration);
if (in_array('--setup', $argv, true)) {
    if ((int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn() !== 0) throw new RuntimeException('Test database is not empty. Use a fresh isolated database.');
    $pdo->exec("INSERT IGNORE INTO users (id,name,email,role,password_hash) VALUES (1,'Test Admin','blog-test@example.invalid','admin',''),(2,'Test Worker','worker-test@example.invalid','worker',''),(3,'Test Customer','customer-test@example.invalid','customer','')");
    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=1')->execute([password_hash('local-test-password', PASSWORD_DEFAULT)]);
    $pdo->exec('DELETE FROM user_sessions');
    foreach ([1 => 'local-blog-admin-test', 2 => 'local-blog-worker-test', 3 => 'local-blog-customer-test'] as $user => $token) $pdo->prepare('INSERT INTO user_sessions (user_id,token_hash,expires_at,last_used_at) VALUES (?,SHA2(?,256),DATE_ADD(NOW(),INTERVAL 2 DAY),NOW())')->execute([$user, $token]);
    $pdo->prepare("INSERT INTO user_sessions (user_id,token_hash,expires_at,last_used_at) VALUES (1,SHA2(?,256),'2000-01-01',NOW())")->execute(['local-blog-expired-test']);
    echo "Isolated test schema and accounts ready.\n";
    exit;
}

$checks = 0;
$created = [];
$media = [];
function expectBlog(bool $value, string $case): void { global $checks; if (!$value) throw new RuntimeException($case); $checks++; echo "PASS {$case}\n"; }
function blogRequest(string $path, string $method = 'GET', ?array $data = null, string $token = '', bool $multipart = false): array
{
    $curl = curl_init('http://127.0.0.1:8123' . $path);
    $headers = $token !== '' ? ['Authorization: Bearer ' . $token] : [];
    if ($data !== null && !$multipart) $headers[] = 'Content-Type: application/json';
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 15]);
    if ($data !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $multipart ? $data : json_encode($data));
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return ['status' => $status, 'json' => json_decode($body, true), 'body' => $body];
}
$admin = 'local-blog-admin-test';
$draft = ['id' => 'new-client-uuid', 'slug' => 'blog-integration-first', 'category' => 'berita', 'author' => 'Tim MAUA', 'status' => 'draft', 'idContent' => ['title' => 'Artikel Uji Pertama', 'excerpt' => 'Ringkasan artikel uji.', 'body' => '<h2 style="text-align:center">Pembuka</h2><p style="text-align:justify">Cerita uji pencarian khusus.</p>'], 'enContent' => []];
$temporaryImage = tempnam(sys_get_temp_dir(), 'maua-blog-');
try {
    expectBlog(blogRequest('/__blog_test_health')['json']['database'] === 'maua_blog_test', 'Server HTTP memakai database uji terpisah');
    expectBlog(blogRequest('/api/blog/index.php')['json']['data']['total'] === 0, 'Blog dimulai kosong tanpa artikel contoh');
    expectBlog(blogRequest('/api/blog/index.php?all=1')['status'] === 401, 'Daftar admin menolak pengunjung tanpa token');
    foreach (['local-blog-worker-test','local-blog-customer-test'] as $token) expectBlog(blogRequest('/api/blog/save.php', 'POST', $draft, $token)['status'] === 403, 'Peran non-admin tidak dapat menulis artikel');
    expectBlog(blogRequest('/api/blog/index.php?all=1', 'GET', null, 'local-blog-expired-test')['status'] === 401, 'Token kedaluwarsa ditolak');
    expectBlog(blogRequest('/api/blog/save.php', 'GET', null, $admin)['status'] === 405, 'Mutasi menolak metode GET');
    $result = blogRequest('/api/blog/save.php', 'POST', $draft, $admin);
    expectBlog($result['status'] === 201 && $result['json']['success'], 'Admin dapat membuat draft');
    $post = $result['json']['data']; $created[] = $post['id'];
    expectBlog(blogRequest('/api/blog/detail.php?slug=' . $post['slug'])['status'] === 404, 'Draft tidak dapat dibaca lewat URL publik');
    expectBlog(blogRequest('/api/blog/index.php')['json']['data']['total'] === 0, 'Draft tidak masuk daftar publik');
    expectBlog(blogRequest('/api/blog/detail.php?id=' . $post['id'] . '&admin=1', 'GET', null, $admin)['json']['data']['id'] === $post['id'], 'Admin dapat membaca draft');
    expectBlog(blogRequest('/api/blog/save.php', 'POST', [...$post, 'status' => 'published'], $admin)['status'] === 422, 'Publish memvalidasi kelengkapan sampul');
    $image = imagecreatetruecolor(1200, 800); imagefill($image, 0, 0, imagecolorallocate($image, 120, 160, 80)); imagepng($image, $temporaryImage); imagedestroy($image);
    expectBlog(blogRequest('/api/blog/upload.php', 'POST', ['image' => new CURLFile($temporaryImage, 'image/png', 'sample.png')])['status'] === 401, 'Upload gambar memerlukan sesi admin');
    $uploaded = blogRequest('/api/blog/upload.php', 'POST', ['image' => new CURLFile($temporaryImage, 'image/png', 'sample.png')], $admin, true);
    expectBlog($uploaded['status'] === 201, 'Upload menghasilkan WebP dan thumbnail');
    $asset = $uploaded['json']['data']; $media[] = $asset;
    expectBlog(blogRequest('/api/blog/upload.php', 'POST', ['image' => new CURLFile($temporaryImage, 'image/png', 'sample.png')], 'local-blog-worker-test', true)['status'] === 403, 'Peran tim tidak dapat mengunggah gambar blog');
    file_put_contents($temporaryImage, '<?php echo "unsafe";');
    expectBlog(blogRequest('/api/blog/upload.php', 'POST', ['image' => new CURLFile($temporaryImage, 'image/jpeg', 'fake.jpg')], $admin, true)['status'] === 422, 'File palsu ditolak berdasarkan isi, bukan ekstensi');
    expectBlog(str_starts_with($asset['url'], '/uploads/blog/') && is_file($root . $asset['url']) && is_file($root . $asset['thumbnailUrl']), 'Gambar disimpan hanya di folder blog');
    $published = [...$post, 'status' => 'published', 'cover' => $asset['url'], 'coverAlt' => 'Foto uji', 'coverWidth' => 50, 'coverAlign' => 'right', 'idContent' => [...$post['idContent'], 'body' => $post['idContent']['body'] . '<figure><img src="' . $asset['url'] . '" style="width:37%" data-align="center" alt="Foto"></figure><script>alert(1)</script>']];
    $saved = blogRequest('/api/blog/save.php', 'POST', $published, $admin);
    expectBlog($saved['status'] === 200 && $saved['json']['data']['status'] === 'published', 'Draft dapat diterbitkan');
    $published = $saved['json']['data'];
    expectBlog(str_contains($published['idContent']['body'], 'width: 37%') && str_contains($published['idContent']['body'], 'text-align: justify') && !str_contains($published['idContent']['body'], '<script'), 'Format tersimpan dan XSS dibuang pada server');
    expectBlog((int) $pdo->query('SELECT COUNT(*) FROM blog_post_media')->fetchColumn() === 1, 'Gambar sampul dan isi dicatat tanpa relasi ganda');
    expectBlog(blogRequest('/api/blog/index.php?search=pencarian&category=berita')['json']['data']['total'] === 1, 'Pencarian isi dan kategori bekerja di server');
    expectBlog(blogRequest('/api/blog/index.php?search=tidak-ada')['json']['data']['total'] === 0, 'Pencarian tanpa hasil mengembalikan daftar kosong');
    foreach ([1, 2] as $number) {
        $copy = [...$published, 'id' => 'new-client-id', 'slug' => 'blog-integration-extra-' . $number, 'enContent' => ['title' => 'English article ' . $number, 'excerpt' => 'English summary.', 'body' => '<h2>English heading</h2><p>English content.</p>']];
        $extra = blogRequest('/api/blog/save.php', 'POST', $copy, $admin);
        expectBlog($extra['status'] === 201, 'Artikel tambahan untuk pagination dibuat lewat API admin');
        $created[] = $extra['json']['data']['id'];
    }
    $firstPage = blogRequest('/api/blog/index.php?limit=2&page=1')['json']['data'];
    $secondPage = blogRequest('/api/blog/index.php?limit=2&page=2')['json']['data'];
    expectBlog(count($firstPage['items']) === 2 && $firstPage['hasMore'] && count($secondPage['items']) === 1 && !$secondPage['hasMore'], 'Pagination server membagi artikel sesuai batas halaman');
    expectBlog(count(array_unique(array_column([...$firstPage['items'], ...$secondPage['items']], 'id'))) === 3, 'Pagination tidak menggandakan atau melewatkan artikel');
    expectBlog($firstPage['items'][0]['hasEnglish'] && $firstPage['items'][0]['enContent']['body'] === '', 'Ringkasan daftar mendukung bahasa Inggris tanpa mengirim seluruh isi');
    $public = blogRequest('/api/blog/detail.php?slug=' . $published['slug']);
    expectBlog($public['status'] === 200 && $public['json']['data']['coverWidth'] === 50, 'Detail publik memakai artikel dan pengaturan gambar tersimpan');
    expectBlog(blogRequest('/api/blog/save.php', 'POST', [...$published, 'version' => 1], $admin)['status'] === 409, 'Versi lama tidak dapat menimpa edit yang lebih baru');
    expectBlog(blogRequest('/api/blog/save.php', 'POST', [...$draft, 'id' => 'new', 'slug' => $published['slug']], $admin)['status'] === 409, 'URL artikel wajib unik');
    expectBlog(blogRequest('/api/blog/save.php', 'POST', [...$published, 'cover' => '/uploads/blog/' . str_repeat('f', 32) . '.webp'], $admin)['status'] === 422, 'Referensi gambar yang tidak terdaftar ditolak');
    $seo = blogRequest('/blog/' . $published['slug']);
    expectBlog($seo['status'] === 200 && str_contains($seo['body'], 'Artikel Uji Pertama') && str_contains($seo['body'], 'NewsArticle') && str_contains($seo['body'], 'Daftar isi'), 'Server merender artikel, daftar isi, dan metadata');
    expectBlog(str_contains(blogRequest('/sitemap.xml')['body'], '/blog/' . $published['slug']), 'Sitemap hanya memuat artikel terbit');
    $trashed = blogRequest('/api/blog/status.php', 'POST', ['id' => $published['id'], 'status' => 'trashed', 'version' => $published['version']], $admin)['json']['data'];
    expectBlog(blogRequest('/api/blog/status.php', 'POST', ['id' => $published['id'], 'status' => 'draft', 'version' => $published['version']], $admin)['status'] === 409, 'Status juga menolak versi artikel yang sudah lama');
    expectBlog(blogRequest('/api/blog/detail.php?slug=' . $published['slug'])['status'] === 404 && blogRequest('/blog/' . $published['slug'])['status'] === 404, 'Artikel di sampah hilang dari API publik dan SEO');
    expectBlog(!str_contains(blogRequest('/sitemap.xml')['body'], '/blog/' . $published['slug']), 'Artikel di sampah dihapus dari sitemap');
    $restored = blogRequest('/api/blog/status.php', 'POST', ['id' => $trashed['id'], 'status' => 'draft', 'version' => $trashed['version']], $admin);
    expectBlog($restored['json']['data']['status'] === 'draft' && is_file($root . $asset['url']), 'Pulihkan sebagai draft tetap mempertahankan gambar');
    expectBlog(blogRequest('/api/users/session.php', 'GET', null, $admin)['json']['data']['role'] === 'admin', 'Session admin yang sudah ada tetap berfungsi');
    $login = blogRequest('/api/users/login.php', 'POST', ['email' => 'blog-test@example.invalid', 'password' => 'local-test-password', 'role' => 'admin']);
    expectBlog($login['status'] === 200 && !empty($login['json']['data']['token']), 'Login admin lama tetap berfungsi');
    echo "{$checks} blog integration checks passed.\n";
} finally {
    foreach ($created as $id) $pdo->prepare('DELETE FROM blog_posts WHERE id=?')->execute([$id]);
    foreach ($media as $asset) {
        $pdo->prepare('DELETE FROM blog_media WHERE id=?')->execute([$asset['id']]);
        foreach (['url','thumbnailUrl'] as $key) {
            $path = $asset[$key];
            if (preg_match('#^/uploads/blog/[a-f0-9]{32}(?:-thumb)?\.webp$#D', $path) && is_file($root . $path)) unlink($root . $path);
        }
    }
    if (is_file($temporaryImage)) unlink($temporaryImage);
}
