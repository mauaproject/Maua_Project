<?php
declare(strict_types=1);

function seoBlogContent(): array
{
    static $content = null;
    if ($content === null) {
        $file = __DIR__ . '/blog-content.json';
        if (!is_file($file)) {
            $file = dirname(__DIR__) . '/src/content/blog.json';
        }
        $content = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }
    return $content;
}

function seoBlogRichText(string $text): string
{
    $safeText = seoEscape($text);
    $safeText = preg_replace('/\*\*([^*]+)\*\*/u', '<strong>$1</strong>', $safeText);
    return (string) preg_replace('/\*([^*]+)\*/u', '<em>$1</em>', $safeText);
}

function seoBlogHtml(): string
{
    $data = seoBlogContent();
    $copy = $data['id'];
    $heading = static fn(array $section): string => '<p class="seo-kicker">' . seoEscape($section['eyebrow']) . '</p><h2>' . nl2br(seoEscape($section['title'])) . '</h2>';
    $paragraphs = static fn(array $items): string => implode('', array_map(static fn(string $text): string => '<p>' . seoBlogRichText($text) . '</p>', $items));

    $html = '<main id="blog-top"><section class="seo-hero"><p class="seo-kicker">' . seoEscape($copy['hero']['eyebrow']) . '</p>';
    $html .= '<h1>' . nl2br(seoEscape($copy['hero']['title'])) . '</h1><p class="seo-lead">' . seoEscape($copy['hero']['copy']) . '</p>';
    $html .= '<a class="seo-button" href="#destinasi">' . seoEscape($copy['hero']['explore']) . '</a></section>';
    $html .= '<nav aria-label="' . seoEscape($copy['contentsAria']) . '"><ul>';
    foreach ($copy['sections'] as $section) {
        $html .= '<li><a href="#' . seoEscape($section['id']) . '">' . seoEscape($section['label']) . '</a></li>';
    }
    $html .= '</ul></nav>';
    $html .= '<section class="seo-section" id="tentang">' . $heading($copy['about']) . $paragraphs($copy['about']['paragraphs']) . '</section>';

    $html .= '<section class="seo-section" id="panduan">' . $heading($copy['guide']) . '<p>' . seoEscape($copy['guide']['intro']) . '</p><div class="seo-columns">';
    foreach ($copy['guide']['services'] as $service) {
        $html .= '<article><h3>' . seoEscape($service['title']) . '</h3><p>' . seoEscape($service['copy']) . '</p><p>' . seoEscape($service['note']) . '</p></article>';
    }
    $html .= '</div><h3 id="booking">' . seoEscape($copy['guide']['bookingTitle']) . '</h3><ol>';
    foreach ($copy['guide']['steps'] as $step) {
        $html .= '<li><h4>' . seoEscape($step['title']) . '</h4><p>' . seoEscape($step['copy']) . '</p></li>';
    }
    $html .= '</ol><p>' . seoEscape($copy['guide']['bookingNote']) . '</p></section>';

    $html .= '<section class="seo-section" id="destinasi">' . $heading($copy['destinations']) . '<p>' . seoEscape($copy['destinations']['intro']) . '</p>';
    foreach ($copy['destinations']['groups'] as $group) {
        $html .= '<section><h3 id="' . seoEscape($group['id']) . '">' . seoEscape($group['title']) . '</h3><p>' . nl2br(seoEscape($group['subtitle'])) . '</p>' . $paragraphs($group['paragraphs']) . '</section>';
    }
    $html .= '<p>' . seoEscape($copy['destinations']['note']) . '</p><a href="/destinasi">' . seoEscape($copy['destinations']['link']) . '</a></section>';

    $html .= '<section class="seo-section" id="review">' . $heading($copy['reviews']) . '<p>' . seoEscape($copy['reviews']['intro']) . '</p><div class="seo-grid">';
    foreach ($data['featuredReviews'] as $review) {
        $html .= '<article class="seo-review"><h3>' . seoEscape($review['name']) . '</h3><p>' . seoEscape($review['trip']) . '</p>';
        $html .= '<p aria-label="' . seoEscape($copy['reviews']['ratingLabel']) . '">' . str_repeat('★', (int) $review['rating']) . '</p>';
        $html .= '<blockquote lang="id">' . seoEscape($review['content']) . '</blockquote><time datetime="' . seoEscape($review['date']) . '">' . seoEscape($review['date']) . '</time></article>';
    }
    $html .= '</div><p>' . seoEscape($copy['reviews']['originalLanguageNote']) . '</p><a href="/reviews">' . seoEscape($copy['reviews']['allLink']) . '</a></section>';

    $html .= '<section class="seo-section" id="persiapan">' . $heading($copy['preparation']) . '<p>' . seoEscape($copy['preparation']['intro']) . '</p><ul>';
    foreach ($copy['preparation']['items'] as $item) {
        $html .= '<li><h3>' . seoEscape($item['title']) . '</h3><p>' . seoEscape($item['copy']) . '</p></li>';
    }
    return $html . '</ul></section></main>';
}
