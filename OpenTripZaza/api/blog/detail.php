<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('GET');
runEndpoint(function (PDO $pdo): void {
    $admin = ($_GET['admin'] ?? '') === '1';
    if ($admin) requireBlogAdmin($pdo);
    try { requireBlogSchema($pdo); }
    catch (InvalidArgumentException $error) {
        if (!$admin) jsonError('Artikel belum dapat dimuat. Silakan coba lagi nanti.', 503);
        throw $error;
    }
    $id = (int) ($_GET['id'] ?? 0);
    $slug = blogText($_GET['slug'] ?? '', 180, 'URL artikel');
    if ($id <= 0 && $slug === '') throw new InvalidArgumentException('Artikel belum dipilih.');
    $where = $id > 0 ? 'p.id=?' : 'p.slug=?';
    if (!$admin) $where .= " AND p.status='published' AND p.published_at<=CURRENT_TIMESTAMP";
    $query = $pdo->prepare(blogSelect() . ' WHERE ' . $where . ' LIMIT 1');
    $query->execute([$id > 0 ? $id : $slug]);
    $row = $query->fetch();
    if (!$row) jsonError('Artikel tidak ditemukan.', 404);
    jsonSuccess(mapBlogPosts($pdo, [$row])[0]);
});
