<?php
declare(strict_types=1);

$databaseFile = __DIR__ . '/api/config/database.php';
if (!is_file($databaseFile)) {
    $databaseFile = dirname(__DIR__) . '/api/config/database.php';
}
require_once $databaseFile;

const SITEMAP_BASE_URL = 'https://mauaproject.com';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

function sitemapEscape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

$today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$urls = [
    ['path' => '/', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '1.0'],
    ['path' => '/open-trip-jogja', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['path' => '/destinasi', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['path' => '/reviews', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
];

try {
    $sql = "SELECT t.id, DATE(COALESCE(t.updated_at, t.created_at, CURRENT_DATE)) AS lastmod
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
            ORDER BY t.id";
    foreach (database()->query($sql)->fetchAll() as $trip) {
        $urls[] = [
            'path' => '/open-trip/' . (int) $trip['id'],
            'lastmod' => (string) ($trip['lastmod'] ?: $today),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }
} catch (Throwable $exception) {
    error_log('Sitemap trip query failed: ' . $exception->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    $loc = SITEMAP_BASE_URL . ($url['path'] === '/' ? '/' : $url['path']);
    echo "  <url>\n";
    echo '    <loc>' . sitemapEscape($loc) . "</loc>\n";
    echo '    <lastmod>' . sitemapEscape($url['lastmod']) . "</lastmod>\n";
    echo '    <changefreq>' . sitemapEscape($url['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . sitemapEscape($url['priority']) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
