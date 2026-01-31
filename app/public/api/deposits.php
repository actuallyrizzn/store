<?php

declare(strict_types=1);

/**
 * GET /api/deposits.php — List deposits for current user's stores (session).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = requireSession($session);
$stmt = $pdo->prepare('SELECT d.* FROM deposits d JOIN store_users su ON d.store_uuid = su.store_uuid WHERE su.user_uuid = ? AND d.deleted_at IS NULL');
$stmt->execute([$user['uuid']]);
echo json_encode(['deposits' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
