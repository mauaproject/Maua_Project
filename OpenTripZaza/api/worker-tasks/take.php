<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'workerEmail']);
    $pdo->beginTransaction();
    try {
        $userStatement = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND role = 'worker' LIMIT 1");
        $userStatement->execute([strtolower(trim((string) $data['workerEmail']))]);
        $worker = $userStatement->fetch();
        if (!$worker) {
            throw new InvalidArgumentException('Akun tim tidak ditemukan.');
        }
        $taskStatement = $pdo->prepare('SELECT * FROM worker_tasks WHERE id = ? FOR UPDATE');
        $taskStatement->execute([(int) $data['id']]);
        $task = $taskStatement->fetch();
        if (!$task || $task['status'] !== 'Tersedia' || $task['worker_id']) {
            throw new InvalidArgumentException('Tugas sudah tidak tersedia.');
        }
        $scopeStatement = $pdo->prepare('SELECT COUNT(*) FROM worker_tasks WHERE booking_id = ? AND worker_id = ?');
        $scopeStatement->execute([(int) $task['booking_id'], (int) $worker['id']]);
        if ((int) $scopeStatement->fetchColumn() > 0) {
            throw new InvalidArgumentException('Tim sudah mengambil tugas untuk booking ini.');
        }
        $pdo->prepare("UPDATE worker_tasks SET worker_id=?, status='Diambil', updated_at=CURRENT_TIMESTAMP WHERE id=?")
            ->execute([(int) $worker['id'], (int) $data['id']]);
        $pdo->commit();
        jsonSuccess(['id' => (int) $data['id'], 'status' => 'Diambil', 'worker' => $worker['name']]);
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
});
