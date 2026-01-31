<?php

declare(strict_types=1);

/**
 * Schema migration: create tables, views, seed config.
 * Must live under public/ so it syncs to LEMP. Run via HTTP (GET/POST) or CLI: php schema.php
 * baseDir = parent of public/ (same level as db/).
 */
$baseDir = dirname(__DIR__);
$inc = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
require $inc . 'Env.php';
require $inc . 'Db.php';
require $inc . 'Schema.php';
require $inc . 'Views.php';
require $inc . 'Config.php';
require $inc . 'User.php';

Env::load($baseDir);

$dsn = Env::get('DB_DSN');
if ($dsn && strpos($dsn, 'sqlite') === 0) {
    $path = substr($dsn, 7);
    $pathNormalized = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $dir = ($path !== '' && $path[0] !== '/' && preg_match('#^[a-z]:#i', $path) !== 1)
        ? $baseDir . DIRECTORY_SEPARATOR . dirname($pathNormalized)
        : dirname($pathNormalized);
    if ($dir !== '' && !is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

Db::init($baseDir);

$pdo = Db::pdo();
$schema = new Schema($pdo, Db::isSqlite());
$schema->run();

$views = new Views($pdo, Db::isSqlite());
$views->run();

$config = new Config($pdo);
$config->seedDefaults();

// Optional: seed admin user from .env (ADMIN_USERNAME, ADMIN_PASSWORD) for dev/demo
$adminUsername = Env::get('ADMIN_USERNAME');
$adminPassword = Env::get('ADMIN_PASSWORD') ?? 'admin';
if ($adminUsername !== null && $adminUsername !== '') {
    $userRepo = new User($pdo);
    $existing = $userRepo->findByUsername($adminUsername);
    if ($existing === null) {
        $userRepo->create(User::generateUuid(), $adminUsername, $adminPassword, User::ROLE_ADMIN, null);
    } else {
        $pdo->prepare('UPDATE users SET role = ? WHERE uuid = ?')->execute([User::ROLE_ADMIN, $existing['uuid']]);
        if ($adminPassword !== '') {
            $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare('UPDATE users SET passphrase_hash = ? WHERE uuid = ?')->execute([$hash, $existing['uuid']]);
        }
    }
}

if (php_sapi_name() === 'cli') {
    echo "Schema, views, and config seeded.\n";
} else {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'message' => 'Schema, views, and config seeded.']);
}
