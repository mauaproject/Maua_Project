<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('POST');

function blogImageVariant(GdImage $source, string $directory, string $filename, int $maxEdge, int $quality): array
{
    $scale = min(1, $maxEdge / max(imagesx($source), imagesy($source)));
    $width = max(1, (int) round(imagesx($source) * $scale));
    $height = max(1, (int) round(imagesy($source) * $scale));
    $target = imagecreatetruecolor($width, $height);
    imagealphablending($target, false);
    imagesavealpha($target, true);
    imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
    try {
        if (!imagewebp($target, $directory . '/' . $filename, $quality)) throw new RuntimeException('Gambar tidak dapat disimpan.');
    } finally { imagedestroy($target); }
    return ['width' => $width, 'height' => $height];
}

runEndpoint(function (PDO $pdo): void {
    $admin = requireBlogAdmin($pdo);
    requireBlogSchema($pdo);
    $file = $_FILES['image'] ?? [];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) throw new InvalidArgumentException('Gambar gagal diunggah. Pilih JPG, PNG, atau WebP maksimal 10 MB.');
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 10 * 1024 * 1024) throw new InvalidArgumentException('Ukuran gambar maksimal 10 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) throw new InvalidArgumentException('Format gambar harus JPG, PNG, atau WebP.');
    $dimensions = @getimagesize($file['tmp_name']);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] * $dimensions[1] > 16000000) throw new InvalidArgumentException('Dimensi gambar tidak valid atau melebihi 16 megapiksel.');
    if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) throw new RuntimeException('Aktifkan ekstensi PHP GD dengan WebP untuk upload blog.');
    $source = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
    if (!$source) throw new InvalidArgumentException('File gambar tidak dapat dibaca.');
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($file['tmp_name'])['Orientation'] ?? 1);
        if (in_array($orientation, [2,4,5,7], true)) imageflip($source, in_array($orientation, [2,5,7], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        $angle = [3 => 180, 5 => -90, 6 => -90, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($angle) {
            $rotated = imagerotate($source, $angle, 0);
            if ($rotated) { imagedestroy($source); $source = $rotated; }
        }
    }
    $directory = dirname(__DIR__, 2) . '/uploads/blog';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('Folder uploads/blog tidak dapat dibuat.');
    $id = bin2hex(random_bytes(16));
    $imageName = $id . '.webp';
    $thumbnailName = $id . '-thumb.webp';
    try {
        $detail = blogImageVariant($source, $directory, $imageName, 1600, 82);
        blogImageVariant($source, $directory, $thumbnailName, 480, 76);
        $url = '/uploads/blog/' . $imageName;
        $thumbnailUrl = '/uploads/blog/' . $thumbnailName;
        $size = filesize($directory . '/' . $imageName);
        $query = $pdo->prepare('INSERT INTO blog_media (id,image_url,thumbnail_url,width,height,size_bytes,uploaded_by) VALUES (?,?,?,?,?,?,?)');
        $query->execute([$id, $url, $thumbnailUrl, $detail['width'], $detail['height'], $size, (int) $admin['id']]);
    } catch (Throwable $error) {
        foreach ([$imageName, $thumbnailName] as $filename) if (is_file($directory . '/' . $filename)) unlink($directory . '/' . $filename);
        throw $error;
    } finally { imagedestroy($source); }
    jsonSuccess(['id' => $id, 'url' => $url, 'thumbnailUrl' => $thumbnailUrl, 'width' => $detail['width'], 'height' => $detail['height']], 201);
});
