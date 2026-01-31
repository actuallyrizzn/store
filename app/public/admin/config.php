<?php

declare(strict_types=1);

/**
 * GET /admin/config.php — List config (admin).
 * POST /admin/config.php — Update config (admin).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../includes/Config.php';

header('Content-Type: application/json');

$user = requireAdmin($session);
$config = new Config($pdo);

$configKeys = ['pending_duration', 'completed_duration', 'stuck_duration', 'completion_tolerance', 'partial_refund_resolver_percent', 'gold_account_commission', 'silver_account_commission', 'bronze_account_commission', 'free_account_commission'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $out = [];
    foreach ($configKeys as $k) {
        $out[$k] = $config->get($k);
    }
    echo json_encode($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($configKeys as $k) {
        if (isset($_POST[$k])) {
            $config->set($k, (string) $_POST[$k]);
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
