<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = jsonInput();
    requiredFields($data, ['id', 'workerEmail']);

    $pdo->beginTransaction();
    try {
        $userStatement = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'worker' LIMIT 1");
        $userStatement->execute([strtolower(trim((string) $data['workerEmail']))]);
        $worker = $userStatement->fetch();
        if (!$worker) {
            throw new InvalidArgumentException('Akun tim tidak ditemukan.');
        }

        $taskStatement = $pdo->prepare('SELECT * FROM worker_tasks WHERE id = ? FOR UPDATE');
        $taskStatement->execute([(int) $data['id']]);
        $task = $taskStatement->fetch();
        if (!$task) {
            jsonError('Tugas tim tidak ditemukan.', 404);
        }
        if ((int) $task['worker_id'] !== (int) $worker['id']) {
            throw new InvalidArgumentException('Job ini bukan milik akun tim ini.');
        }
        if ($task['status'] === 'Selesai') {
            throw new InvalidArgumentException('Job yang sudah selesai tidak bisa dicancel.');
        }

        $pdo->prepare(
            "UPDATE worker_tasks
             SET worker_id=NULL, status='Tersedia', result_link=NULL, drive_link=NULL,
                 proof_photo_url=NULL, proof_photo_name=NULL, completion_checked=0,
                 result_status=NULL, completed_at=NULL, completed_by_name=NULL,
                 updated_at=CURRENT_TIMESTAMP
             WHERE id=?"
        )->execute([(int) $data['id']]);

        $pdo->commit();
        jsonSuccess(['id' => (int) $data['id'], 'status' => 'Tersedia']);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
});
