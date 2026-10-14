<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');
runEndpoint(function (PDO $pdo): void {
    $admin = requireBlogAdmin($pdo);
    requireBlogSchema($pdo);
    $data = jsonInput();
    $values = blogInput($data);
    $rawId = $data['id'] ?? '';
    if (!is_string($rawId) && !is_int($rawId)) throw new InvalidArgumentException('ID artikel tidak valid.');
    $id = ctype_digit((string) $rawId) ? (int) $rawId : 0;
    $creating = $id === 0;
    $pdo->beginTransaction();
    try {
        if ($id > 0) {
            $lock = $pdo->prepare('SELECT id,status,version,published_at FROM blog_posts WHERE id=? FOR UPDATE');
            $lock->execute([$id]);
            $existing = $lock->fetch();
            if (!$existing) { $pdo->rollBack(); jsonError('Artikel tidak ditemukan.', 404); }
            if ($existing['status'] === 'trashed') throw new InvalidArgumentException('Pulihkan artikel dari sampah sebelum mengeditnya.');
            if ((int) ($data['version'] ?? 0) !== (int) $existing['version']) { $pdo->rollBack(); jsonError('Artikel sudah diubah pada sesi lain. Muat ulang artikel sebelum menyimpan.', 409); }
            $values['published_at'] = $existing['published_at'] ?: ($values['status'] === 'published' ? date('Y-m-d H:i:s') : null);
            $values['updated_by'] = (int) $admin['id'];
            $columns = array_map(fn(string $column): string => $column . '=?', array_keys($values));
            $query = $pdo->prepare('UPDATE blog_posts SET ' . implode(',', $columns) . ',version=version+1,updated_at=CURRENT_TIMESTAMP WHERE id=?');
            $query->execute([...array_values($values), $id]);
        } else {
            $values['published_at'] = $values['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            $values['created_by'] = $values['updated_by'] = (int) $admin['id'];
            $query = $pdo->prepare('INSERT INTO blog_posts (' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')');
            $query->execute(array_values($values));
            $id = (int) $pdo->lastInsertId();
        }
        $mediaIds = blogMediaReferences($pdo, $values);
        $pdo->prepare('DELETE FROM blog_post_media WHERE post_id=?')->execute([$id]);
        $reference = $pdo->prepare('INSERT INTO blog_post_media (post_id,media_id) VALUES (?,?)');
        foreach ($mediaIds as $mediaId) $reference->execute([$id, $mediaId]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($error instanceof PDOException && $error->getCode() === '23000') jsonError('URL artikel sudah digunakan. Pilih URL lain.', 409);
        throw $error;
    }
    jsonSuccess(blogPostById($pdo, $id), $creating ? 201 : 200);
});
