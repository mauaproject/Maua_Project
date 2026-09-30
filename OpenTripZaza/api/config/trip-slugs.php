<?php
declare(strict_types=1);

function tripSlugBase(string $name): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $slug = strtolower((string) ($ascii === false ? $name : $ascii));
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
    return $slug !== '' ? $slug : 'trip';
}

function tripIdLetters(int $id): string
{
    $letters = '';
    do {
        $letters = chr(97 + ($id % 26)) . $letters;
        $id = intdiv($id, 26);
    } while ($id > 0);
    return $letters;
}

function tripSlugMap(PDO $pdo): array
{
    $rows = $pdo->query("SELECT id, name FROM trips WHERE status <> 'Dihapus' ORDER BY id")->fetchAll();
    $bases = [];
    $counts = [];
    foreach ($rows as $row) {
        $base = tripSlugBase((string) $row['name']);
        $bases[(int) $row['id']] = $base;
        $counts[$base] = ($counts[$base] ?? 0) + 1;
    }
    $reserved = array_fill_keys(array_values($bases), true);
    $used = [];
    $map = [];
    foreach ($bases as $id => $base) {
        $slug = $base;
        if ($counts[$base] > 1 || isset($used[$slug])) {
            $slug = $base . '-' . tripIdLetters($id);
            while (isset($reserved[$slug]) || isset($used[$slug])) {
                $slug .= 'a';
            }
        }
        $map[$id] = $slug;
        $used[$slug] = true;
    }
    return $map;
}

function tripSlugFor(PDO $pdo, int $id): string
{
    static $map = null;
    $map ??= tripSlugMap($pdo);
    return $map[$id] ?? '';
}
