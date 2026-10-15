<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('GET');
runEndpoint(function (PDO $pdo): void {
    $admin = ($_GET['all'] ?? '') === '1';
    if ($admin) requireBlogAdmin($pdo);
    try { requireBlogSchema($pdo); }
    catch (InvalidArgumentException $error) {
        if (!$admin) jsonError('Artikel belum dapat dimuat. Silakan coba lagi nanti.', 503);
        throw $error;
    }
    if ($admin) {
        jsonSuccess(mapBlogPosts($pdo, $pdo->query(blogSelect() . ' ORDER BY p.updated_at DESC,p.id DESC')->fetchAll()));
    }
    if (isset($_GET['related'])) {
        $relatedId = blogText($_GET['related'], 20, 'Artikel terkait');
        if (!ctype_digit($relatedId)) throw new InvalidArgumentException('ID artikel terkait tidak valid.');
        $source = blogPostById($pdo, (int) $relatedId);
        if (!$source || $source['status'] !== 'published' || strtotime($source['publishedAt']) > time()) jsonSuccess(['items' => [], 'total' => 0, 'page' => 1, 'hasMore' => false]);
        $items = blogRelatedPosts($pdo, $source, (int) ($_GET['limit'] ?? 3));
        jsonSuccess(['items' => $items, 'total' => count($items), 'page' => 1, 'hasMore' => false]);
    }
    $where = ["p.status='published'", 'p.published_at <= CURRENT_TIMESTAMP'];
    $params = [];
    $category = blogText($_GET['category'] ?? '', 32, 'Kategori');
    if ($category !== '' && $category !== 'all') { $where[] = 'p.category=?'; $params[] = $category; }
    $search = blogText($_GET['search'] ?? '', 200, 'Kata kunci');
    foreach (array_filter(preg_split('/\s+/u', $search) ?: []) as $word) {
        $keywordMatch = blogHasKeywords($pdo) ? ' OR EXISTS(SELECT 1 FROM blog_post_keywords k WHERE k.post_id=p.id AND LOCATE(?,k.keyword)>0)' : '';
        $where[] = "(LOCATE(?, CONCAT_WS(' ',p.title_id,p.excerpt_id,p.body_id,p.title_en,p.excerpt_en,p.body_en,p.author)) > 0" . $keywordMatch . ')';
        $params[] = $word;
        if ($keywordMatch !== '') $params[] = $word;
    }
    $conditions = implode(' AND ', $where);
    $count = $pdo->prepare('SELECT COUNT(*) FROM blog_posts p WHERE ' . $conditions);
    $count->execute($params);
    $total = (int) $count->fetchColumn();
    $page = max(1, min(100000, (int) ($_GET['page'] ?? 1)));
    $limit = max(1, min(24, (int) ($_GET['limit'] ?? 8)));
    $query = $pdo->prepare(blogSelect() . ' WHERE ' . $conditions . ' ORDER BY p.published_at DESC,p.id DESC LIMIT ? OFFSET ?');
    foreach ($params as $index => $value) $query->bindValue($index + 1, $value);
    $query->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $query->bindValue(count($params) + 2, ($page - 1) * $limit, PDO::PARAM_INT);
    $query->execute();
    jsonSuccess(['items' => mapBlogPosts($pdo, $query->fetchAll(), true), 'total' => $total, 'page' => $page, 'hasMore' => $page * $limit < $total]);
});
