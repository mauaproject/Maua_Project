<?php
declare(strict_types=1);

$blogHelper = __DIR__ . '/api/blog/helper.php';
if (!is_file($blogHelper)) $blogHelper = dirname(__DIR__) . '/api/blog/helper.php';
require_once $blogHelper;

function seoBlogPosts(PDO $pdo): array
{
    return array_map(fn(array $row): array => mapBlogPost($row, true), $pdo->query(blogSelect() . " WHERE p.status='published' AND p.published_at<=CURRENT_TIMESTAMP ORDER BY p.published_at DESC,p.id DESC LIMIT 8")->fetchAll());
}

function seoBlogArticle(PDO $pdo, string $slug): ?array
{
    $query = $pdo->prepare(blogSelect() . " WHERE p.slug=? AND p.status='published' AND p.published_at<=CURRENT_TIMESTAMP LIMIT 1");
    $query->execute([$slug]);
    $row = $query->fetch();
    return $row ? mapBlogPost($row) : null;
}

function seoBlogHtml(array $posts, ?array $article = null): string
{
    if ($article) {
        $content = $article['idContent'];
        $body = blogHtml($content['body']);
        $index = 0;
        $contents = [];
        $body = preg_replace_callback('#<h([23])([^>]*)>(.*?)</h\1>#is', function (array $match) use (&$index, &$contents): string {
            $title = html_entity_decode(strip_tags($match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $slug = strtolower((string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title));
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
            $id = 'section-' . (++$index) . '-' . $slug;
            $contents[] = '<li><a href="#' . seoEscape($id) . '">' . seoEscape($title) . '</a></li>';
            return '<h' . $match[1] . $match[2] . ' id="' . seoEscape($id) . '">' . $match[3] . '</h' . $match[1] . '>';
        }, $body) ?? $body;
        $html = '<main><article class="seo-detail"><a href="/blog">Kembali ke blog</a><h1>' . seoEscape($content['title']) . '</h1><p class="seo-lead">' . seoEscape($content['excerpt']) . '</p>';
        $html .= '<p>' . seoEscape($article['author']) . ' · <time datetime="' . seoEscape($article['publishedAt']) . '">' . seoEscape(substr($article['publishedAt'], 0, 10)) . '</time></p>';
        if ($article['cover']) $html .= '<img class="seo-hero-image" src="' . seoEscape(seoAbsoluteImage($article['cover'])) . '" alt="' . seoEscape($article['coverAlt']) . '">';
        if ($contents) $html .= '<nav aria-label="Daftar isi"><h2>Daftar isi</h2><ul>' . implode('', $contents) . '</ul></nav>';
        return $html . '<div class="seo-blog-body">' . $body . '</div></article></main>';
    }
    $html = '<main><section class="seo-hero"><p class="seo-kicker">MAUA Journal</p><h1>Blog dan Cerita Perjalanan</h1><p class="seo-lead">Inspirasi perjalanan, panduan destinasi, dan kabar terbaru dari MAUA Project.</p></section>';
    if (!$posts) return $html . '<section class="seo-section"><h2>Cerita baru sedang disiapkan</h2><p>Cerita perjalanan dan informasi terbaru akan hadir di sini.</p></section></main>';
    $html .= '<section class="seo-section"><h2>Artikel terbaru</h2><div class="seo-grid">';
    foreach ($posts as $post) {
        $html .= '<article class="seo-card"><a href="/blog/' . seoEscape($post['slug']) . '">';
        if ($post['cover']) $html .= '<img src="' . seoEscape(seoAbsoluteImage($post['thumbnail'])) . '" alt="' . seoEscape($post['coverAlt']) . '" loading="lazy">';
        $html .= '<h3>' . seoEscape($post['idContent']['title']) . '</h3></a><p>' . seoEscape($post['category']) . ' · <time datetime="' . seoEscape($post['publishedAt']) . '">' . seoEscape(substr($post['publishedAt'], 0, 10)) . '</time></p></article>';
    }
    return $html . '</div></section></main>';
}
