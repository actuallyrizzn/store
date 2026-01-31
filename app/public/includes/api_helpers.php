<?php

declare(strict_types=1);

/**
 * Helpers for API scripts. Use after bootstrap.php (expects $apiKeyRepo, $session).
 */

function requireApiKeyAndRateLimit(object $apiKeyRepo): array
{
    $key = getApiKeyFromRequest();
    if ($key === null) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Missing API key']);
        exit;
    }
    $user = $apiKeyRepo->validate($key);
    if ($user === null) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
    if (!$apiKeyRepo->checkRateLimit((int) $user['api_key_id'])) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }
    $apiKeyRepo->recordRequest((int) $user['api_key_id']);
    return $user;
}

function requireSession(object $session): array
{
    $session->start();
    $user = $session->getUser();
    if ($user === null) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Login required']);
        exit;
    }
    return $user;
}

function requireAdmin(object $session): array
{
    $user = requireSession($session);
    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Admin only']);
        exit;
    }
    return $user;
}
