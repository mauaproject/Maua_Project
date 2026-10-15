<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/blog/helper.php';

function checkBlog(bool $condition, string $case): void
{
    if (!$condition) throw new RuntimeException($case);
    echo "PASS {$case}\n";
}
function rejectsBlog(callable $operation, string $case): void
{
    try { $operation(); } catch (InvalidArgumentException) { checkBlog(true, $case); return; }
    throw new RuntimeException($case . ': expected validation error');
}
$image = '/uploads/blog/' . str_repeat('a', 32) . '.webp';
$clean = blogHtml('<h2 style="text-align:center;color:red" onclick="alert(1)">Cerita &amp; perjalanan</h2><p style="text-align:justify">Teks <u>garis bawah</u> <strike>coret</strike></p><script>alert(1)</script><iframe src="https://example.com"></iframe><a href="javascript:alert(1)">Link</a><img src="' . $image . '" style="width:37%;position:fixed" data-align="right" onerror="alert(1)" alt="Gambar">');
checkBlog(!str_contains($clean, 'onclick') && !str_contains($clean, 'onerror') && !str_contains($clean, '<script') && !str_contains($clean, '<iframe') && !str_contains($clean, 'javascript:'), 'HTML aktif dan atribut berbahaya dibuang');
checkBlog(str_contains($clean, 'text-align: center') && str_contains($clean, 'text-align: justify') && str_contains($clean, 'width: 37%') && str_contains($clean, 'data-align="right"'), 'Perataan teks dan resize gambar tetap tersimpan');
checkBlog(!str_contains($clean, 'color:') && !str_contains($clean, 'position:') && str_contains($clean, '<u>') && str_contains($clean, '<strike>'), 'Hanya format editor yang diizinkan');
checkBlog(str_contains($clean, 'Cerita &amp; perjalanan'), 'Teks Unicode dan entity tetap terbaca');
foreach (['https://example.com/a.webp', 'data:image/png;base64,AAAA', '/uploads/trips/a.webp', '/uploads/blog/../../file.php', '//example.com/a.webp'] as $url) rejectsBlog(fn() => blogHtml('<img src="' . $url . '">'), 'Tolak sumber gambar di luar folder blog: ' . $url);
$draft = ['slug' => 'artikel-pertama', 'category' => 'berita', 'author' => 'Tim MAUA', 'status' => 'draft', 'idContent' => ['title' => 'Artikel Pertama', 'body' => '<h2>Pembuka</h2><p>Cerita pertama.</p>'], 'enContent' => []];
checkBlog(blogInput($draft)['cover_url'] === null, 'Draft dapat disimpan sebelum sampul lengkap');
rejectsBlog(fn() => blogInput([...$draft, 'status' => 'published']), 'Publish menolak artikel yang belum lengkap');
rejectsBlog(fn() => blogInput([...$draft, 'slug' => '../admin']), 'URL artikel menolak path traversal');
rejectsBlog(fn() => blogInput([...$draft, 'status' => 'trashed']), 'Pindah sampah memakai endpoint status tersendiri');
$published = [...$draft, 'status' => 'published', 'cover' => $image, 'coverAlt' => 'Cahaya goa', 'coverWidth' => 500, 'coverAlign' => 'right', 'idContent' => [...$draft['idContent'], 'excerpt' => 'Cerita pertama bersama MAUA.']];
checkBlog(blogInput($published)['cover_width'] === 100, 'Ukuran sampul dibatasi ke lebar artikel');
checkBlog(blogKeywords(' Wisata Goa Jomblang, Vertical   Caving Jogja, wisata goa jomblang, ,') === ['Wisata Goa Jomblang', 'Vertical Caving Jogja'], 'Keyword dipisah koma, dirapikan, dan dideduplikasi tanpa membedakan kapital');
checkBlog(blogKeywords([]) === [] && blogKeywords(null) === [], 'Keyword opsional untuk artikel lama');
checkBlog(blogKeywords(['Petualangan & alam', 'Cahaya "surga"', str_repeat('旅', 80)])[2] === str_repeat('旅', 80), 'Keyword mendukung teks Unicode dan tanda baca');
rejectsBlog(fn() => blogKeywords(['<script>alert(1)</script>']), 'Keyword menolak HTML aktif');
rejectsBlog(fn() => blogKeywords([['invalid']]), 'Keyword menolak tipe data selain teks');
rejectsBlog(fn() => blogKeywords(array_map(fn(int $index): string => 'Topik ' . $index, range(1, 11))), 'Keyword dibatasi 10 frasa');
rejectsBlog(fn() => blogKeywords(str_repeat('a', 81)), 'Panjang setiap keyword dibatasi');

echo "Blog content checks passed.\n";
