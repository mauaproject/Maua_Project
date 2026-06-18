<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $role = $_GET['role'] ?? null;
    $sql = 'SELECT id, name, email, whatsapp, role, address, age, gender, health_notes FROM users';
    $params = [];
    if ($role && in_array($role, ['admin', 'customer', 'worker'], true)) {
        $sql .= ' WHERE role = ?';
        $params[] = $role;
    }
    $sql .= ' ORDER BY name';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $users = array_map(static fn(array $user): array => [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'whatsapp' => $user['whatsapp'] ?? '',
        'role' => $user['role'] === 'worker' ? 'pekerja' : $user['role'],
        'address' => $user['address'] ?? '',
        'age' => $user['age'] ?? '',
        'gender' => $user['gender'] ?? '',
        'healthNotes' => $user['health_notes'] ?? '',
    ], $statement->fetchAll());
    jsonSuccess($users);
});
