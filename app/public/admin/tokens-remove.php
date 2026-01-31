<?php

declare(strict_types=1);

/**
 * POST /admin/tokens-remove.php — Remove accepted token (admin).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = requireAdmin($session);
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

$pdo->prepare('DELETE FROM accepted_tokens WHERE id = ?')->execute([$id]);
echo json_encode(['ok' => true]);
