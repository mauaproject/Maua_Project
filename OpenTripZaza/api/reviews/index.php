<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
require_once __DIR__ . '/helper.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $showAll = ($_GET['all'] ?? '') === '1';
    $showMine = ($_GET['mine'] ?? '') === '1';
    if ($showAll) {
        $adminEmail = strtolower(trim((string) ($_GET['admin_email'] ?? '')));
        $adminStatement = $pdo->prepare("SELECT id FROM users WHERE email=? AND role='admin' LIMIT 1");
        $adminStatement->execute([$adminEmail]);
        if (!$adminStatement->fetch()) {
            jsonError('Akses admin diperlukan.', 403);
        }
    }
    $email = strtolower(trim((string) ($_GET['email'] ?? '')));
    $where = [];
    $params = [];
    if (!$showAll && !$showMine) {
        $where[] = "r.status='approved'";
    }
    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email tidak valid.');
        }
        $where[] = 'r.reviewer_email=?';
        $params[] = $email;
    }
    if ($showMine) {
        $userId = (int) ($_GET['user_id'] ?? 0);
        $userStatement = $pdo->prepare("SELECT id FROM users WHERE id=? AND email=? AND role='customer' LIMIT 1");
        $userStatement->execute([$userId, $email]);
        if (!$userStatement->fetch()) {
            jsonError('Akses user tidak valid.', 403);
        }
        $where[] = 'r.user_id=?';
        $params[] = $userId;
    }
    $sql = 'SELECT r.*, COALESCE(t.name, r.trip_label) trip_name
            FROM reviews r
            LEFT JOIN trips t ON t.id=r.trip_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY r.created_at DESC, r.id DESC';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    jsonSuccess(array_map('mapReview', $statement->fetchAll()));
});
