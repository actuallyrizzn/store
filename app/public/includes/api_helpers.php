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

function requireAgentOrApiKey(AgentIdentity $agentIdentity, object $apiKeyRepo, \PDO $pdo, Hooks $hooks): array
{
    $token = $_SERVER['HTTP_X_AGENT_IDENTITY'] ?? '';
    if ($token !== '') {
        $profile = $agentIdentity->verifyToken($token);
        if ($profile === null) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid agent identity']);
            exit;
        }
        $agentId = (string) ($profile['id'] ?? '');
        $agentName = (string) ($profile['name'] ?? '');
        if ($agentId === '') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid agent identity']);
            exit;
        }
        $oneMinAgo = date('Y-m-d H:i:s', time() - 60);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM agent_requests WHERE agent_id = ? AND requested_at > ?');
        $stmt->execute([$agentId, $oneMinAgo]);
        $recentCount = (int) $stmt->fetchColumn();
        if ($recentCount >= 60) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit exceeded']);
            exit;
        }
        $pdo->prepare('INSERT INTO agent_requests (agent_id, requested_at) VALUES (?, ?)')->execute([$agentId, date('Y-m-d H:i:s')]);
        $result = $agentIdentity->getOrCreateUser($agentId, $agentName);
        if ($result === null || empty($result['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid agent identity']);
            exit;
        }
        if ($recentCount === 0) {
            $hooks->fire('agent_first_request', ['agent_id' => $agentId]);
        }
        if (!empty($result['is_new'])) {
            $hooks->fire('agent_identity_verified', ['agent_id' => $agentId, 'user_uuid' => $result['user']['uuid'] ?? null]);
        }
        return $result['user'];
    }
    return requireApiKeyAndRateLimit($apiKeyRepo);
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
