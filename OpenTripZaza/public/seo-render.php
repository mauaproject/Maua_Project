<?php
declare(strict_types=1);

$databaseFile = __DIR__ . '/api/config/database.php';
if (!is_file($databaseFile)) {
    $databaseFile = dirname(__DIR__) . '/api/config/database.php';
}
require_once $databaseFile;

const SEO_SITE_URL = 'https://mauaproject.com';
const SEO_SITE_NAME = 'MAUA Project';
const SEO_WHATSAPP = 'https://wa.me/62882005881248';
const SEO_INSTAGRAM = 'https://www.instagram.com/mauaproject/';

function seoEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function seoText(mixed $value): string
{
    $text = trim(strip_tags((string) $value));
    return preg_replace('/\s+/u', ' ', $text) ?: '';
}

function seoExcerpt(mixed $value, int $maxLength = 158): string
{
    $text = seoText($value);
    if ($text === '') {
        return '';
    }
    $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    if ($length <= $maxLength) {
        return $text;
    }
    $short = function_exists('mb_substr') ? mb_substr($text, 0, $maxLength - 1) : substr($text, 0, $maxLength - 1);
    $short = preg_replace('/\s+\S*$/u', '', $short) ?: $short;
    return rtrim($short, " .,;:-") . '…';
}

function seoCurrency(mixed $value): string
{
    return 'Rp' . number_format((float) $value, 0, ',', '.');
}

function seoAbsoluteImage(mixed $value): string
{
    $url = trim((string) $value);
    if ($url === '') {
        return SEO_SITE_URL . '/favicon.svg';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    return SEO_SITE_URL . '/' . ltrim($url, '/');
}

function seoDecodeList(mixed $value): array
{
    if (is_array($value)) {
        return array_values(array_filter(array_map('seoText', $value)));
    }
    $raw = trim((string) $value);
    if ($raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map('seoText', $decoded)));
    }
    return array_values(array_filter(array_map('seoText', preg_split('/\r\n|\r|\n/', $raw) ?: [])));
}

function seoPublicTrips(PDO $pdo, int $limit = 16): array
{
    $sql = "SELECT t.id, t.name, t.trip_type, t.destination_id, t.description_id, t.price,
                   t.available_start_date, t.available_end_date,
                   (SELECT COALESCE(NULLIF(ti.thumbnail_url, ''), ti.image_url)
                    FROM trip_images ti WHERE ti.trip_id = t.id
                    ORDER BY ti.sort_order, ti.id LIMIT 1) AS image_url
            FROM trips t
            WHERE t.status = 'Tersedia'
              AND (
                (
                  t.trip_type = 'open'
                  AND EXISTS (
                    SELECT 1 FROM trip_schedules ts
                    WHERE ts.trip_id = t.id
                      AND ts.status = 'active'
                      AND ts.quota > ts.booked_count
                      AND TIMESTAMP(ts.schedule_date, COALESCE(ts.end_time, '23:59:59')) > NOW()
                  )
                )
                OR
                (
                  t.trip_type = 'private'
                  AND t.available_end_date IS NOT NULL
                  AND EXISTS (
                    SELECT 1 FROM trip_sessions tss
                    WHERE tss.trip_id = t.id
                      AND tss.status = 'active'
                      AND TIMESTAMP(t.available_end_date, COALESCE(tss.end_time, '23:59:59')) > NOW()
                  )
                )
              )
            ORDER BY t.updated_at DESC, t.id DESC
            LIMIT " . max(1, min($limit, 40));
    return $pdo->query($sql)->fetchAll();
}

function seoTrip(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare(
        "SELECT t.*,
                (SELECT ti.image_url FROM trip_images ti WHERE ti.trip_id = t.id ORDER BY ti.sort_order, ti.id LIMIT 1) AS image_url
         FROM trips t WHERE t.id = ? AND t.status <> 'Dihapus' LIMIT 1"
    );
    $statement->execute([$id]);
    $trip = $statement->fetch();
    return $trip ?: null;
}

function seoReviews(PDO $pdo, int $limit = 12): array
{
    $statement = $pdo->prepare(
        "SELECT r.reviewer_name, r.rating, r.content, r.created_at, COALESCE(t.name, r.trip_label) AS trip_name
         FROM reviews r LEFT JOIN trips t ON t.id = r.trip_id
         WHERE r.status = 'approved'
         ORDER BY r.created_at DESC, r.id DESC LIMIT ?"
    );
    $statement->bindValue(1, max(1, min($limit, 30)), PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function seoNav(): string
{
    return '<header class="seo-nav"><a class="seo-brand" href="/">MAUA Project</a><nav aria-label="Navigasi utama">'
        . '<a href="/">Beranda</a><a href="/open-trip-jogja">Open Trip Jogja</a>'
        . '<a href="/destinasi">Destinasi</a><a href="/reviews">Ulasan</a></nav></header>';
}

function seoTripCards(array $trips, int $limit = 12): string
{
    if (!$trips) {
        return '<p>Jadwal trip terbaru sedang disiapkan. Hubungi MAUA Project untuk informasi keberangkatan.</p>';
    }
    $cards = '';
    foreach (array_slice($trips, 0, $limit) as $trip) {
        $name = seoText($trip['name'] ?? 'Trip MAUA Project');
        $destination = seoText($trip['destination_id'] ?? 'Yogyakarta');
        $type = ($trip['trip_type'] ?? 'open') === 'private' ? 'Private trip' : 'Open trip';
        $image = trim((string) ($trip['image_url'] ?? ''));
        $cards .= '<article class="seo-card">';
        if ($image !== '') {
            $cards .= '<img src="' . seoEscape(seoAbsoluteImage($image)) . '" alt="' . seoEscape($name . ' di ' . $destination) . '" width="480" height="320" loading="lazy">';
        }
        $cards .= '<div><p class="seo-kicker">' . seoEscape($type) . '</p><h3><a href="/open-trip/' . (int) $trip['id'] . '">' . seoEscape($name) . '</a></h3>';
        $cards .= '<p>' . seoEscape($destination) . ' · Mulai ' . seoEscape(seoCurrency($trip['price'] ?? 0)) . ' per orang</p></div></article>';
    }
    return '<div class="seo-grid">' . $cards . '</div>';
}

function seoFaqs(): array
{
    return [
        ['Apa itu open cave trip?', 'Open cave trip adalah perjalanan jelajah goa dengan jadwal dan kuota yang sudah ditentukan. Peserta dapat mendaftar sendiri atau bersama teman lalu bergabung dengan peserta lain.'],
        ['Apa perbedaan open trip dan private trip?', 'Open trip berbagi jadwal kegiatan dengan peserta lain. Private trip ditujukan untuk rombongan sendiri dan pilihan tanggalnya mengikuti ketersediaan paket.'],
        ['Apakah cave trip cocok untuk pemula?', 'Tingkat kesulitan setiap goa berbeda. Periksa deskripsi paket, ikuti briefing dan arahan pemandu, serta sampaikan kondisi kesehatan sebelum memesan.'],
        ['Apa saja yang termasuk dalam harga trip?', 'Fasilitas berbeda pada setiap paket. Rincian pemandu, perlengkapan keselamatan, konsumsi, dokumentasi, transportasi lokal, dan add-on tercantum pada halaman masing-masing trip.'],
        ['Bagaimana cara memesan open trip Jogja?', 'Pilih trip dan jadwal yang tersedia, buat akun, lengkapi data peserta, lalu kirim pembayaran sesuai petunjuk. Tim MAUA Project akan memeriksa dan mengonfirmasi pesanan.'],
        ['Di mana lokasi kegiatan dan meeting point?', 'Sebagian besar program cave trip berada di kawasan Gunungkidul, Yogyakarta. Lokasi dan meeting point spesifik mengikuti detail serta informasi teknis pada paket yang dipilih.'],
    ];
}

function seoFaqMarkup(): string
{
    $items = '';
    foreach (seoFaqs() as [$question, $answer]) {
        $items .= '<details><summary>' . seoEscape($question) . '</summary><p>' . seoEscape($answer) . '</p></details>';
    }
    return '<section class="seo-section"><h2>Pertanyaan tentang open cave trip</h2><div class="seo-faq">' . $items . '</div></section>';
}

function seoOrganizationSchema(): array
{
    return [
        '@type' => 'Organization',
        '@id' => SEO_SITE_URL . '/#organization',
        'name' => SEO_SITE_NAME,
        'url' => SEO_SITE_URL . '/',
        'logo' => SEO_SITE_URL . '/favicon.svg',
        'description' => 'Penyedia open cave trip, wisata goa, dan private cave tour di Yogyakarta.',
        'telephone' => '+62882005881248',
        'sameAs' => [SEO_INSTAGRAM],
        'areaServed' => [
            ['@type' => 'AdministrativeArea', 'name' => 'Daerah Istimewa Yogyakarta'],
            ['@type' => 'AdministrativeArea', 'name' => 'Gunungkidul'],
        ],
        'contactPoint' => [[
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'telephone' => '+62882005881248',
            'availableLanguage' => ['Indonesian', 'English'],
        ]],
    ];
}

function seoBaseSchema(string $canonical, string $title, string $description): array
{
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            seoOrganizationSchema(),
            [
                '@type' => 'WebSite',
                '@id' => SEO_SITE_URL . '/#website',
                'url' => SEO_SITE_URL . '/',
                'name' => SEO_SITE_NAME,
                'inLanguage' => 'id-ID',
                'publisher' => ['@id' => SEO_SITE_URL . '/#organization'],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $canonical . '#webpage',
                'url' => $canonical,
                'name' => $title,
                'description' => $description,
                'inLanguage' => 'id-ID',
                'isPartOf' => ['@id' => SEO_SITE_URL . '/#website'],
                'about' => ['@id' => SEO_SITE_URL . '/#organization'],
            ],
        ],
    ];
}

function seoFaqSchema(): array
{
    return [
        '@type' => 'FAQPage',
        '@id' => SEO_SITE_URL . '/#faq',
        'mainEntity' => array_map(static fn(array $faq): array => [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
        ], seoFaqs()),
    ];
}

function seoPageHtml(string $path, array $trips, array $reviews, ?array $trip): string
{
    $nav = seoNav();
    if ($trip !== null) {
        $name = seoText($trip['name'] ?? 'Trip MAUA Project');
        $destination = seoText($trip['destination_id'] ?? 'Yogyakarta');
        $description = seoText($trip['description_id'] ?? '');
        $activities = seoDecodeList($trip['activities_id'] ?? '');
        $facilities = seoDecodeList($trip['facilities_id'] ?? '');
        $list = static function (array $items): string {
            return '<ul>' . implode('', array_map(static fn(string $item): string => '<li>' . seoEscape($item) . '</li>', $items)) . '</ul>';
        };
        $image = trim((string) ($trip['image_url'] ?? ''));
        $content = '<main><article class="seo-detail"><p class="seo-kicker">' . seoEscape(($trip['trip_type'] ?? 'open') === 'private' ? 'Private cave tour Jogja' : 'Open cave trip Jogja') . '</p>';
        $content .= '<h1>' . seoEscape($name) . '</h1><p class="seo-lead">' . seoEscape($destination) . '</p>';
        if ($image !== '') {
            $content .= '<img class="seo-hero-image" src="' . seoEscape(seoAbsoluteImage($image)) . '" alt="' . seoEscape($name . ' di ' . $destination) . '" width="1200" height="750">';
        }
        $content .= '<section><h2>Deskripsi trip</h2><p>' . seoEscape($description ?: 'Lihat jadwal, harga, fasilitas, dan informasi pemesanan paket ini bersama MAUA Project.') . '</p></section>';
        if ($activities) {
            $content .= '<section><h2>Aktivitas</h2>' . $list($activities) . '</section>';
        }
        if ($facilities) {
            $content .= '<section><h2>Fasilitas</h2>' . $list($facilities) . '</section>';
        }
        $content .= '<aside class="seo-cta"><strong>Mulai ' . seoEscape(seoCurrency($trip['price'] ?? 0)) . ' per orang</strong><a href="/daftar/' . (int) $trip['id'] . '">Pilih jadwal dan pesan</a></aside></article></main>';
        return '<div class="seo-prerender">' . $nav . $content . '</div>';
    }

    if ($path === '/open-trip-jogja') {
        $content = '<main><section class="seo-hero"><p class="seo-kicker">Open trip & private trip Yogyakarta</p><h1>Open Trip Jogja untuk Petualangan Goa Gunungkidul</h1>';
        $content .= '<p class="seo-lead">Temukan jadwal open cave trip, wisata goa vertikal, cave tubing, dan private cave tour di Yogyakarta. Bandingkan tujuan, harga, kuota, serta fasilitas sebelum memesan.</p>';
        $content .= '<p><a class="seo-button" href="/destinasi">Lihat semua paket trip</a></p></section>';
        $content .= '<section class="seo-section"><h2>Pilih open trip atau private trip Jogja</h2><div class="seo-columns"><article><h3>Open cave trip</h3><p>Cocok untuk peserta individu atau kelompok kecil yang ingin bergabung pada jadwal dan kuota yang telah tersedia.</p></article><article><h3>Private cave tour</h3><p>Cocok untuk rombongan sendiri yang membutuhkan pilihan tanggal sesuai rentang ketersediaan paket.</p></article><article><h3>Informasi transparan</h3><p>Setiap halaman paket menampilkan tujuan, aktivitas, fasilitas, harga, jadwal, dan slot yang dapat dipilih.</p></article></div></section>';
        $content .= '<section class="seo-section"><h2>Jadwal open trip Jogja yang tersedia</h2>' . seoTripCards($trips) . '</section>';
        $content .= '<section class="seo-section"><h2>Cara booking trip</h2><ol><li>Pilih destinasi dan jenis trip.</li><li>Periksa tingkat aktivitas, fasilitas, jadwal, dan harga.</li><li>Buat akun lalu lengkapi data peserta.</li><li>Kirim pembayaran dan tunggu konfirmasi tim.</li></ol></section>';
        $content .= seoFaqMarkup() . '</main>';
        return '<div class="seo-prerender">' . $nav . $content . '</div>';
    }

    if ($path === '/destinasi') {
        $content = '<main><section class="seo-hero"><p class="seo-kicker">Paket wisata Yogyakarta</p><h1>Paket Open Trip Jogja, Wisata Goa, dan Private Tour</h1>';
        $content .= '<p class="seo-lead">Jelajahi paket aktif MAUA Project di Gunungkidul dan Yogyakarta. Buka detail trip untuk memeriksa aktivitas, fasilitas, harga, jadwal, dan ketersediaan peserta.</p></section>';
        $content .= '<section class="seo-section"><h2>Destinasi dan aktivitas yang tersedia</h2>' . seoTripCards($trips, 16) . '</section></main>';
        return '<div class="seo-prerender">' . $nav . $content . '</div>';
    }

    if ($path === '/reviews') {
        $items = '';
        foreach ($reviews as $review) {
            $items .= '<article class="seo-review"><p aria-label="' . (int) $review['rating'] . ' dari 5 bintang">' . str_repeat('★', (int) $review['rating']) . '</p>';
            $items .= '<blockquote>' . seoEscape($review['content'] ?? '') . '</blockquote><p><strong>' . seoEscape($review['reviewer_name'] ?? 'Peserta') . '</strong> · ' . seoEscape($review['trip_name'] ?? 'Trip MAUA Project') . '</p></article>';
        }
        $content = '<main><section class="seo-hero"><p class="seo-kicker">Pengalaman peserta</p><h1>Review Open Trip dan Cave Tour MAUA Project</h1><p class="seo-lead">Baca pengalaman peserta setelah mengikuti trip bersama MAUA Project.</p></section>';
        $content .= '<section class="seo-section"><h2>Ulasan peserta</h2><div class="seo-grid">' . ($items ?: '<p>Belum ada ulasan publik.</p>') . '</div></section></main>';
        return '<div class="seo-prerender">' . $nav . $content . '</div>';
    }

    $content = '<main><section class="seo-hero"><p class="seo-kicker">Cave tour Yogyakarta</p><h1>Open Cave Trip Jogja & Wisata Goa Yogyakarta</h1>';
    $content .= '<p class="seo-lead">MAUA Project membantu kamu menemukan open trip Jogja, wisata goa Gunungkidul, dan private cave tour. Cek tujuan, jadwal, harga, kuota, serta fasilitas sebelum booking.</p>';
    $content .= '<p><a class="seo-button" href="/open-trip-jogja">Cari open trip Jogja</a></p></section>';
    $content .= '<section class="seo-section"><h2>Open cave trip dan private cave tour terbaru</h2><p>Pilih trip sesuai pengalaman yang kamu cari, mulai dari jelajah goa vertikal hingga kegiatan air. Detail setiap paket menjelaskan aktivitas, fasilitas, dan persiapan peserta.</p>' . seoTripCards($trips) . '</section>';
    $content .= seoFaqMarkup() . '</main>';
    return '<div class="seo-prerender">' . $nav . $content . '</div>';
}

function seoPrerenderStyles(): string
{
    return '<style id="seo-prerender-style">.seo-prerender{max-width:1180px;margin:0 auto;padding:20px 24px 72px;font:16px/1.65 Arial,sans-serif;color:#241a08}.seo-nav{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:16px 0}.seo-nav nav{display:flex;flex-wrap:wrap;gap:16px}.seo-nav a,.seo-card a{color:#704a0e}.seo-brand{font-size:22px;font-weight:800;text-decoration:none}.seo-hero{padding:80px 0 40px;max-width:850px}.seo-hero h1,.seo-detail h1{font-size:clamp(38px,6vw,68px);line-height:1.06;margin:8px 0 20px}.seo-lead{font-size:20px;max-width:780px}.seo-kicker{font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#986515}.seo-button,.seo-cta a{display:inline-block;border-radius:999px;background:#241a08;color:#fff!important;padding:12px 20px;text-decoration:none}.seo-section{padding:32px 0}.seo-section h2,.seo-detail h2{font-size:30px;line-height:1.2}.seo-grid,.seo-columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px}.seo-card,.seo-columns article,.seo-review,.seo-faq details,.seo-cta{border:1px solid #d8bf8d;border-radius:20px;background:#fff;padding:18px}.seo-card img{width:100%;height:190px;object-fit:cover;border-radius:14px}.seo-card h3{margin:6px 0}.seo-card h3 a{text-decoration:none}.seo-faq{display:grid;gap:12px}.seo-faq summary{font-weight:700;cursor:pointer}.seo-detail{max-width:880px;margin:0 auto;padding:80px 0}.seo-hero-image{width:100%;height:auto;border-radius:24px;margin:20px 0}.seo-detail section{padding:16px 0}.seo-cta{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:24px}@media(max-width:700px){.seo-nav{align-items:flex-start;flex-direction:column}.seo-hero{padding-top:40px}.seo-cta{align-items:flex-start;flex-direction:column}}</style>';
}

$requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = '/' . trim($requestPath, '/');
if ($path === '//') {
    $path = '/';
}

$isTripPage = preg_match('#^/open-trip/(\d+)$#', $path, $tripMatch) === 1;
$publicPaths = ['/', '/open-trip-jogja', '/destinasi', '/reviews'];
$privatePrefixes = ['/admin', '/tim', '/akun', '/payment-confirmation', '/daftar', '/verify-email', '/forgot-password', '/reset-password', '/login', '/signup', '/customer'];
$isPrivatePath = false;
foreach ($privatePrefixes as $prefix) {
    if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
        $isPrivatePath = true;
        break;
    }
}

$title = 'Open Cave Trip Jogja & Wisata Goa | MAUA Project';
$description = 'Temukan open trip Jogja, wisata goa Gunungkidul, dan private cave tour bersama MAUA Project. Cek jadwal, harga, kuota, fasilitas, lalu booking online.';
$canonicalPath = $path;
$robots = 'index, follow';
$statusCode = 200;
$trips = [];
$reviews = [];
$trip = null;
$databaseError = null;

try {
    $pdo = database();
    if ($isTripPage) {
        $trip = seoTrip($pdo, (int) $tripMatch[1]);
    } elseif (in_array($path, ['/', '/open-trip-jogja', '/destinasi'], true)) {
        $trips = seoPublicTrips($pdo);
    } elseif ($path === '/reviews') {
        $reviews = seoReviews($pdo);
    }
} catch (Throwable $exception) {
    $databaseError = $exception;
    error_log('SEO renderer database query failed: ' . $exception->getMessage());
}

if ($path === '/open-trip-jogja') {
    $title = 'Open Trip Jogja & Cave Trip Gunungkidul | MAUA Project';
    $description = 'Cari open trip Jogja, open cave trip, wisata goa Gunungkidul, dan private cave tour. Bandingkan jadwal, harga, kuota, fasilitas, lalu booking online.';
} elseif ($path === '/destinasi') {
    $title = 'Paket Open Trip Jogja & Wisata Goa | MAUA Project';
    $description = 'Lihat paket open trip Jogja, wisata goa, cave tubing, dan private cave tour di Yogyakarta beserta jadwal, harga, fasilitas, dan ketersediaannya.';
} elseif ($path === '/reviews') {
    $title = 'Review Open Trip & Cave Tour | MAUA Project';
    $description = 'Baca ulasan dan pengalaman peserta open trip, wisata goa, dan private cave tour bersama MAUA Project di Yogyakarta.';
} elseif ($isTripPage && $trip !== null) {
    $name = seoText($trip['name'] ?? 'Trip MAUA Project');
    $destination = seoText($trip['destination_id'] ?? 'Yogyakarta');
    $title = seoExcerpt($name . ' | Open Trip Jogja – MAUA Project', 62);
    $description = seoExcerpt($trip['description_id'] ?? '', 155);
    if ($description === '') {
        $description = seoExcerpt('Cek jadwal, harga, fasilitas, dan booking ' . $name . ' di ' . $destination . ' bersama MAUA Project.', 155);
    }
} elseif ($isTripPage && $trip === null && $databaseError === null) {
    $statusCode = 404;
    $robots = 'noindex, follow';
    $title = 'Trip Tidak Ditemukan | MAUA Project';
    $description = 'Trip yang kamu cari tidak ditemukan atau sudah tidak tersedia.';
    $canonicalPath = '/destinasi';
} elseif ($isPrivatePath) {
    $robots = 'noindex, nofollow';
    $title = 'Area Akun | MAUA Project';
    $description = 'Area akun dan pengelolaan MAUA Project.';
    $canonicalPath = '/';
} elseif (!in_array($path, $publicPaths, true) && !$isTripPage) {
    $statusCode = 404;
    $robots = 'noindex, follow';
    $title = 'Halaman Tidak Ditemukan | MAUA Project';
    $description = 'Halaman yang kamu cari tidak ditemukan.';
    $canonicalPath = '/';
}

http_response_code($statusCode);
header('Content-Type: text/html; charset=utf-8');
header('Vary: Accept-Encoding');
if (str_starts_with($robots, 'noindex')) {
    header('X-Robots-Tag: ' . $robots);
}

$canonical = SEO_SITE_URL . ($canonicalPath === '/' ? '/' : $canonicalPath);
$image = $trip !== null ? seoAbsoluteImage($trip['image_url'] ?? '') : (!empty($trips[0]['image_url']) ? seoAbsoluteImage($trips[0]['image_url']) : SEO_SITE_URL . '/favicon.svg');
$schema = seoBaseSchema($canonical, $title, $description);

if (in_array($path, ['/', '/open-trip-jogja'], true)) {
    $schema['@graph'][] = seoFaqSchema();
}
if ($path === '/open-trip-jogja' || $path === '/destinasi' || $path === '/') {
    $schema['@graph'][] = [
        '@type' => 'ItemList',
        'name' => 'Paket trip MAUA Project',
        'itemListElement' => array_map(static fn(array $item, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => SEO_SITE_URL . '/open-trip/' . (int) $item['id'],
            'name' => seoText($item['name'] ?? ''),
        ], $trips, array_keys($trips)),
    ];
}
if ($trip !== null) {
    $schema['@graph'][] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => SEO_SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Destinasi', 'item' => SEO_SITE_URL . '/destinasi'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => seoText($trip['name'] ?? ''), 'item' => $canonical],
        ],
    ];
    $schema['@graph'][] = [
        '@type' => 'Product',
        '@id' => $canonical . '#trip',
        'name' => seoText($trip['name'] ?? ''),
        'description' => seoExcerpt($trip['description_id'] ?? '', 500),
        'image' => [$image],
        'category' => ($trip['trip_type'] ?? 'open') === 'private' ? 'Private Cave Tour' : 'Open Cave Trip',
        'brand' => ['@id' => SEO_SITE_URL . '/#organization'],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => 'IDR',
            'price' => (float) ($trip['price'] ?? 0),
            'availability' => ($trip['status'] ?? '') === 'Tersedia' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ],
    ];
}

$indexFile = __DIR__ . '/index.html';
if (!is_file($indexFile)) {
    $indexFile = dirname(__DIR__) . '/index.html';
}
$html = is_file($indexFile) ? (string) file_get_contents($indexFile) : '<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>MAUA Project</title></head><body><div id="root"></div></body></html>';

$replaceTag = static function (string $pattern, string $replacement) use (&$html): void {
    $updated = preg_replace($pattern, $replacement, $html, 1);
    if (is_string($updated)) {
        $html = $updated;
    }
};

$replaceTag('#<title>.*?</title>#is', '<title>' . seoEscape($title) . '</title>');
$replaceTag('#<meta\s+name=["\']description["\'][^>]*>#i', '<meta name="description" content="' . seoEscape($description) . '">');
$replaceTag('#<meta\s+name=["\']robots["\'][^>]*>#i', '<meta name="robots" content="' . seoEscape($robots) . '">');
$replaceTag('#<meta\s+name=["\']googlebot["\'][^>]*>#i', '<meta name="googlebot" content="' . seoEscape($robots) . ', max-snippet:-1, max-image-preview:large, max-video-preview:-1">');
$replaceTag('#<link\s+rel=["\']canonical["\'][^>]*>#i', '<link rel="canonical" href="' . seoEscape($canonical) . '">');
$replaceTag('#<meta\s+property=["\']og:title["\'][^>]*>#i', '<meta property="og:title" content="' . seoEscape($title) . '">');
$replaceTag('#<meta\s+property=["\']og:description["\'][^>]*>#i', '<meta property="og:description" content="' . seoEscape($description) . '">');
$replaceTag('#<meta\s+property=["\']og:url["\'][^>]*>#i', '<meta property="og:url" content="' . seoEscape($canonical) . '">');
$replaceTag('#<meta\s+property=["\']og:image["\'][^>]*>#i', '<meta property="og:image" content="' . seoEscape($image) . '">');
$replaceTag('#<meta\s+name=["\']twitter:title["\'][^>]*>#i', '<meta name="twitter:title" content="' . seoEscape($title) . '">');
$replaceTag('#<meta\s+name=["\']twitter:description["\'][^>]*>#i', '<meta name="twitter:description" content="' . seoEscape($description) . '">');
$replaceTag('#<meta\s+name=["\']twitter:image["\'][^>]*>#i', '<meta name="twitter:image" content="' . seoEscape($image) . '">');
$html = preg_replace('#<script\b(?=[^>]*\btype=["\']application/ld\+json["\'])[^>]*>.*?</script>#is', '', $html) ?: $html;
$jsonLd = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$html = str_replace('</head>', seoPrerenderStyles() . '<script id="maua-structured-data" type="application/ld+json">' . $jsonLd . '</script></head>', $html);

if ($statusCode === 404) {
    $body = '<div class="seo-prerender">' . seoNav() . '<main><section class="seo-hero"><h1>Halaman tidak ditemukan</h1><p>Periksa kembali alamat halaman atau lihat paket trip yang tersedia.</p><p><a class="seo-button" href="/destinasi">Lihat destinasi</a></p></section></main></div>';
} elseif ($isPrivatePath) {
    $body = '';
} else {
    $body = seoPageHtml($path, $trips, $reviews, $trip);
}

$html = preg_replace('#<div\s+id=["\']root["\']>\s*</div>#i', '<div id="root">' . $body . '</div>', $html, 1) ?: $html;
echo $html;
