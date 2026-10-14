<?php
declare(strict_types=1);

function requireBlogAdmin(PDO $pdo): array
{
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user) jsonError('Silakan login sebagai admin.', 401);
    if (($user['role'] ?? '') !== 'admin') jsonError('Akses admin diperlukan.', 403);
    return $user;
}

function requireBlogSchema(PDO $pdo): void
{
    try { $pdo->query('SELECT id FROM blog_posts LIMIT 0'); }
    catch (PDOException $error) {
        if ($error->getCode() === '42S02') throw new InvalidArgumentException('Jalankan migrasi 2026-10-14-blog.sql untuk mengaktifkan blog.');
        throw $error;
    }
}

function blogText(mixed $value, int $limit, string $label): string
{
    if (!is_string($value) && $value !== null) throw new InvalidArgumentException("{$label} tidak valid.");
    $text = trim(strip_tags((string) $value));
    if (mb_strlen($text) > $limit) throw new InvalidArgumentException("{$label} maksimal {$limit} karakter.");
    return $text;
}

function blogImagePath(string $url): string
{
    if (!preg_match('#^/uploads/blog/[a-f0-9]{32}(?:-thumb)?\.webp$#D', $url)) {
        throw new InvalidArgumentException('Gunakan gambar yang diunggah melalui editor blog.');
    }
    return $url;
}

function blogHtml(string $html): string
{
    if (strlen($html) > 1000000) throw new InvalidArgumentException('Isi artikel terlalu panjang (maksimal 1 MB per bahasa).');
    if ($html === '') return '';
    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8"><div id="blog-sanitizer-root">' . $html . '</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = $document->getElementById('blog-sanitizer-root');
    if (!$root) return '';
    $allowed = ['p','div','h2','h3','strong','b','em','i','u','s','strike','ul','ol','li','blockquote','br','hr','figure','figcaption','img','a'];
    $forbidden = ['script','style','iframe','object','embed','svg','math','form','input','button','template','link','meta','base'];
    $alignable = ['p','div','h2','h3','li','blockquote','figcaption'];
    $clean = function (DOMNode $node) use (&$clean, $allowed, $forbidden, $alignable): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) { $node->removeChild($child); continue; }
            if (!$child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (in_array($tag, $forbidden, true)) { $node->removeChild($child); continue; }
            $clean($child);
            if (!in_array($tag, $allowed, true)) {
                while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                $node->removeChild($child);
                continue;
            }
            $href = trim($child->getAttribute('href'));
            $src = trim($child->getAttribute('src'));
            $alt = mb_substr($child->getAttribute('alt'), 0, 200);
            $imageAlignment = $child->getAttribute('data-align');
            $style = $child->getAttribute('style');
            $alignment = $child->getAttribute('align');
            if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right|justify)\s*(?:;|$)/i', $style, $match)) $alignment = strtolower($match[1]);
            while ($child->attributes->length) $child->removeAttributeNode($child->attributes->item(0));
            if (in_array($tag, $alignable, true) && in_array($alignment, ['left','center','right','justify'], true)) $child->setAttribute('style', "text-align: {$alignment};");
            if ($tag === 'a') {
                if (!preg_match('/[\x00-\x20\x7f]/', $href) && preg_match('#^(https?://|mailto:|/(?!/)|\#)#i', $href)) $child->setAttribute('href', $href);
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            if ($tag === 'img') {
                $child->setAttribute('src', blogImagePath($src));
                $child->setAttribute('alt', $alt);
                $child->setAttribute('loading', 'lazy');
                if (preg_match('/(?:^|;)\s*width\s*:\s*(\d{1,3}(?:\.\d+)?)%\s*(?:;|$)/i', $style, $match)) {
                    $width = max(10, min(100, (float) $match[1]));
                    $child->setAttribute('style', "width: {$width}%;");
                }
                $child->setAttribute('data-align', in_array($imageAlignment, ['left','center','right'], true) ? $imageAlignment : 'left');
            }
        }
    };
    $clean($root);
    $result = '';
    foreach ($root->childNodes as $child) $result .= $document->saveHTML($child);
    return trim($result);
}

function blogInput(array $data): array
{
    $slug = blogText($data['slug'] ?? '', 180, 'URL artikel');
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) throw new InvalidArgumentException('URL artikel hanya boleh berisi huruf kecil, angka, dan tanda hubung.');
    $category = blogText($data['category'] ?? '', 32, 'Kategori');
    if (!in_array($category, ['berita','destinasi','panduan','cerita'], true)) throw new InvalidArgumentException('Kategori artikel tidak valid.');
    $status = blogText($data['status'] ?? 'draft', 16, 'Status');
    if (!in_array($status, ['draft','published'], true)) throw new InvalidArgumentException('Status artikel tidak valid.');
    $author = blogText($data['author'] ?? '', 100, 'Penulis');
    if ($author === '') throw new InvalidArgumentException('Nama penulis wajib diisi.');
    $values = ['slug' => $slug, 'category' => $category, 'author' => $author, 'status' => $status];
    foreach (['id','en'] as $language) {
        $content = $data[$language . 'Content'] ?? [];
        if (!is_array($content)) throw new InvalidArgumentException('Konten artikel tidak valid.');
        foreach (['title' => 180, 'excerpt' => 300, 'seoTitle' => 70, 'seoDescription' => 160] as $field => $limit) {
            $column = ['seoTitle' => 'seo_title', 'seoDescription' => 'seo_description'][$field] ?? $field;
            $values[$column . '_' . $language] = blogText($content[$field] ?? '', $limit, $field);
        }
        if (!is_string($content['body'] ?? '')) throw new InvalidArgumentException('Isi artikel tidak valid.');
        $values['body_' . $language] = blogHtml($content['body'] ?? '');
    }
    if ($values['title_id'] === '') throw new InvalidArgumentException('Judul Bahasa Indonesia wajib diisi.');
    $cover = blogText($data['cover'] ?? '', 512, 'Gambar sampul');
    $values['cover_url'] = $cover === '' ? null : blogImagePath($cover);
    $values['cover_alt'] = blogText($data['coverAlt'] ?? '', 200, 'Deskripsi gambar');
    $values['cover_width'] = max(10, min(100, (int) ($data['coverWidth'] ?? 100)));
    $values['cover_align'] = in_array($data['coverAlign'] ?? '', ['left','center','right'], true) ? $data['coverAlign'] : 'center';
    if ($status === 'published' && ($values['excerpt_id'] === '' || trim(html_entity_decode(strip_tags($values['body_id']), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '' || !$values['cover_url'] || $values['cover_alt'] === '')) {
        throw new InvalidArgumentException('Lengkapi ringkasan, isi artikel, gambar sampul, dan deskripsi gambar sebelum menerbitkan.');
    }
    return $values;
}

function blogMediaReferences(PDO $pdo, array $values): array
{
    $urls = $values['cover_url'] ? [$values['cover_url']] : [];
    foreach (['body_id','body_en'] as $column) {
        preg_match_all('/<img\b[^>]*\bsrc="([^"]+)"/i', $values[$column], $matches);
        foreach ($matches[1] as $url) $urls[] = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $urls = array_values(array_unique($urls));
    if (!$urls) return [];
    $query = $pdo->prepare('SELECT id FROM blog_media WHERE image_url IN (' . implode(',', array_fill(0, count($urls), '?')) . ')');
    $query->execute($urls);
    $ids = $query->fetchAll(PDO::FETCH_COLUMN);
    if (count($ids) !== count($urls)) throw new InvalidArgumentException('Ada gambar yang tidak terdaftar. Unggah ulang gambar melalui editor blog.');
    return $ids;
}

function blogSelect(): string
{
    return 'SELECT p.*, m.thumbnail_url AS cover_thumbnail_url FROM blog_posts p LEFT JOIN blog_media m ON m.image_url=p.cover_url';
}

function blogDate(?string $value): ?string
{
    if (!$value) return null;
    return (new DateTimeImmutable($value, new DateTimeZone('Asia/Jakarta')))->format(DateTimeInterface::ATOM);
}

function mapBlogPost(array $row, bool $summary = false): array
{
    $post = [
        'id' => (string) $row['id'], 'slug' => $row['slug'], 'category' => $row['category'], 'author' => $row['author'], 'status' => $row['status'],
        'cover' => $row['cover_url'] ?? '', 'thumbnail' => $row['cover_thumbnail_url'] ?? $row['cover_url'] ?? '', 'coverAlt' => $row['cover_alt'],
        'coverWidth' => (int) $row['cover_width'], 'coverAlign' => $row['cover_align'], 'version' => (int) $row['version'],
        'createdAt' => blogDate($row['created_at']), 'updatedAt' => blogDate($row['updated_at']), 'publishedAt' => blogDate($row['published_at']),
        'hasEnglish' => trim($row['title_en']) !== '' && trim(strip_tags($row['body_en'])) !== '',
    ];
    foreach (['id','en'] as $language) $post[$language . 'Content'] = [
        'title' => $row['title_' . $language], 'excerpt' => $row['excerpt_' . $language], 'body' => $summary ? '' : $row['body_' . $language],
        'seoTitle' => $row['seo_title_' . $language], 'seoDescription' => $row['seo_description_' . $language],
    ];
    return $post;
}

function blogPostById(PDO $pdo, int $id): ?array
{
    $query = $pdo->prepare(blogSelect() . ' WHERE p.id=?');
    $query->execute([$id]);
    $row = $query->fetch();
    return $row ? mapBlogPost($row) : null;
}
