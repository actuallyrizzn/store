<?php

declare(strict_types=1);

/**
 * Shared bootstrap for API and admin scripts. LEMP: one script per endpoint; each includes this.
 * baseDir = app/ (parent of public/).
 */
$baseDir = dirname(__DIR__, 2);
$inc = __DIR__ . DIRECTORY_SEPARATOR;

require $inc . 'Env.php';
require $inc . 'Db.php';
require $inc . 'Session.php';
require $inc . 'User.php';
require $inc . 'ApiKey.php';
require $inc . 'AgentIdentity.php';
require $inc . 'Hooks.php';

Env::load($baseDir);
Db::init($baseDir);

$pdo = Db::pdo();
$session = new Session($baseDir);
$userRepo = new User($pdo);
$apiKeyRepo = new ApiKey($pdo);
$agentIdentity = new AgentIdentity($pdo, $userRepo);
$hooks = new Hooks($pdo);

function getApiKeyFromRequest(): ?string
{
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', trim($auth), $m)) {
        return trim($m[1]);
    }
    $key = $_SERVER['HTTP_X_API_KEY'] ?? null;
    if ($key !== null && $key !== '') {
        return $key;
    }
    return isset($_GET['token']) ? (string) $_GET['token'] : null;
}
