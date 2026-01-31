<?php

declare(strict_types=1);

/**
 * GET /admin/tokens.php — List accepted tokens (admin).
 * POST /admin/tokens.php — Add token (admin).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json');

$user = requireAdmin($session);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT id, chain_id, symbol, contract_address, created_at FROM accepted_tokens ORDER BY chain_id, symbol');
    echo json_encode(['tokens' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chainId = (int) ($_POST['chain_id'] ?? 0);
    $symbol = trim((string) ($_POST['symbol'] ?? ''));
    $contractAddress = trim((string) ($_POST['contract_address'] ?? ''));
    if ($chainId <= 0 || $symbol === '') {
        http_response_code(400);
        echo json_encode(['error' => 'chain_id and symbol required']);
        exit;
    }
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO accepted_tokens (chain_id, symbol, contract_address, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$chainId, $symbol, $contractAddress ?: null, $now]);
    echo json_encode(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
