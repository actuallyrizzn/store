<?php

declare(strict_types=1);

/**
 * GET /api/disputes.php — List disputes (session).
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
$stmt = $pdo->query('SELECT uuid, status, resolver_user_uuid, created_at FROM disputes WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 50');
echo json_encode(['disputes' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
