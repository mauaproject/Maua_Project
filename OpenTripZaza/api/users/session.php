<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/helpers.php';
requireMethod('GET');

runEndpoint(function (PDO $pdo): void {
    $user = userFromSessionToken($pdo, bearerToken());
    if (!$user) {
        jsonError('Session tidak valid atau sudah expired.', 401);
    }

    jsonSuccess(userPayload($user));
});
