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

function blogKeywords(mixed $value): array
{
    if ($value === null || $value === '') return [];
    if (is_string($value)) $value = explode(',', $value);
    if (!is_array($value) || array_values($value) !== $value) throw new InvalidArgumentException('Keyword harus berupa daftar frasa yang dipisahkan koma.');
    $keywords = [];
    $seen = [];
    foreach ($value as $phrase) {
        if (!is_string($phrase) || preg_match('/[<>\x00-\x1f\x7f,]/u', $phrase)) throw new InvalidArgumentException('Keyword hanya boleh berisi teks. Pisahkan setiap frasa dengan koma.');
        $phrase = trim((string) preg_replace('/\s+/u', ' ', $phrase));
        if ($phrase === '') continue;
        if (mb_strlen($phrase) > 80) throw new InvalidArgumentException('Setiap keyword maksimal 80 karakter.');
        $key = mb_strtolower($phrase, 'UTF-8');
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $keywords[] = $phrase;
        if (count($keywords) > 10) throw new InvalidArgumentException('Maksimal 10 keyword per artikel.');
    }
    return $keywords;
}

function blogHasKeywords(PDO $pdo): bool
{
    static $cache = null;
    $cache ??= new WeakMap();
    if (isset($cache[$pdo])) return $cache[$pdo];
    try { $pdo->query('SELECT post_id FROM blog_post_keywords LIMIT 0'); }
    catch (PDOException $error) {
        if ($error->getCode() === '42S02') return $cache[$pdo] = false;
        throw $error;
    }
    return $cache[$pdo] = true;
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

function mapBlogPosts(PDO $pdo, array $rows, bool $summary = false): array
{
    $keywords = [];
    if ($rows && blogHasKeywords($pdo)) {
        $ids = array_column($rows, 'id');
        $query = $pdo->prepare('SELECT post_id,keyword FROM blog_post_keywords WHERE post_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY post_id,sort_order');
        $query->execute($ids);
        foreach ($query->fetchAll() as $row) $keywords[(string) $row['post_id']][] = $row['keyword'];
    }
    return array_map(fn(array $row): array => mapBlogPost([...$row, 'keywords' => $keywords[(string) $row['id']] ?? []], $summary), $rows);
}

function blogRelatedPosts(PDO $pdo, array $source, int $limit = 3): array
{
    $where = "p.status='published' AND p.published_at<=CURRENT_TIMESTAMP AND p.id<>?";
    $params = [(int) $source['id']];
    $order = '';
    if (blogHasKeywords($pdo) && !empty($source['keywords'])) {
        $shared = 'SELECT COUNT(DISTINCT target.keyword) FROM blog_post_keywords target JOIN blog_post_keywords source ON source.keyword=target.keyword WHERE target.post_id=p.id AND source.post_id=?';
        $where .= ' AND (p.category=? OR (' . $shared . ')>0)';
        array_push($params, $source['category'], (int) $source['id']);
        $order = '(' . $shared . ') DESC, ';
        $params[] = (int) $source['id'];
    } else {
        $where .= ' AND p.category=?';
        $params[] = $source['category'];
    }
    $params[] = $source['category'];
    $query = $pdo->prepare(blogSelect() . ' WHERE ' . $where . ' ORDER BY ' . $order . '(p.category=?) DESC,p.published_at DESC,p.id DESC LIMIT ?');
    foreach ($params as $index => $value) $query->bindValue($index + 1, $value);
    $query->bindValue(count($params) + 1, max(1, min(24, $limit)), PDO::PARAM_INT);
    $query->execute();
    return mapBlogPosts($pdo, $query->fetchAll(), true);
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
        'keywords' => $row['keywords'] ?? [],
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
    return $row ? mapBlogPosts($pdo, [$row])[0] : null;
}
