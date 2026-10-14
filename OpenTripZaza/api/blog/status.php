<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');
runEndpoint(function (PDO $pdo): void {
    $admin = requireBlogAdmin($pdo);
    requireBlogSchema($pdo);
    $data = jsonInput();
    $status = (string) ($data['status'] ?? '');
    if (!in_array($status, ['draft','trashed'], true)) throw new InvalidArgumentException('Status tidak valid. Terbitkan artikel melalui editor.');
    $id = (int) ($data['id'] ?? 0);
    $query = $pdo->prepare('UPDATE blog_posts SET status=?,deleted_at=?,updated_by=?,version=version+1,updated_at=CURRENT_TIMESTAMP WHERE id=? AND version=?');
    $query->execute([$status, $status === 'trashed' ? date('Y-m-d H:i:s') : null, (int) $admin['id'], $id, (int) ($data['version'] ?? 0)]);
    if (!$query->rowCount()) {
        if (!blogPostById($pdo, $id)) jsonError('Artikel tidak ditemukan.', 404);
        jsonError('Artikel sudah diubah pada sesi lain. Muat ulang daftar artikel.', 409);
    }
    jsonSuccess(blogPostById($pdo, $id));
});
