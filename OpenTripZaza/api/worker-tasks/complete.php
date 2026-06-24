<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('POST');

runEndpoint(function (PDO $pdo): void {
    $data = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') ? jsonInput() : $_POST;
    requiredFields($data, ['id']);
    $taskId = (int) $data['id'];
    $status = $data['status'] ?? 'Selesai';
    if (!in_array($status, ['Diambil', 'Sedang Berjalan', 'Selesai'], true)) {
        throw new InvalidArgumentException('Status tugas tidak valid.');
    }

    $proofPath = $data['proofPhotoUrl'] ?? null;
    $proofName = $data['proofPhotoName'] ?? null;
    if (isset($_FILES['proof']) && ($_FILES['proof']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $stored = storeUploadedImage($_FILES['proof'], 'worker-proofs');
        $proofPath = $stored['path'];
        $proofName = basename((string) $_FILES['proof']['name']);
    }

    $statement = $pdo->prepare(
        'UPDATE worker_tasks SET status=?, result_link=?, drive_link=?, proof_photo_url=?, proof_photo_name=?,
         completion_checked=?, result_status=?, completed_at=?, completed_by_name=?, updated_at=CURRENT_TIMESTAMP
         WHERE id=?'
    );
    $isComplete = $status === 'Selesai';
    $statement->execute([
        $status,
        $data['resultLink'] ?? $data['driveLink'] ?? null,
        $data['driveLink'] ?? $data['resultLink'] ?? null,
        $proofPath,
        $proofName,
        boolValue($data['completionChecked'] ?? $isComplete),
        $data['resultStatus'] ?? ($isComplete ? 'completed' : 'in_progress'),
        $isComplete ? ($data['completedAt'] ?? date('Y-m-d H:i:s')) : null,
        $data['completedByName'] ?? null,
        $taskId,
    ]);
    if ($statement->rowCount() === 0) {
        jsonError('Tugas tim tidak ditemukan.', 404);
    }
    jsonSuccess(['id' => $taskId, 'status' => $status, 'proofPhotoUrl' => $proofPath]);
});
