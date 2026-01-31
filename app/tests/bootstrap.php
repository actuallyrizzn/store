<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap: sets up test .env in app/, loads classes, initializes test DB.
 * Uses app/ as base so E2E includes resolve correctly. Overwrites app/.env for test run.
 */
$appDir = dirname(__DIR__);
define('TEST_BASE_DIR', $appDir);

// Backup existing .env and write test .env (restored in shutdown)
$envPath = $appDir . DIRECTORY_SEPARATOR . '.env';
$envBackup = $envPath . '.backup.' . getmypid();
if (file_exists($envPath)) {
    copy($envPath, $envBackup);
}
$testDbPath = $appDir . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'test.sqlite';
$testDbDir = dirname($testDbPath);
if (!is_dir($testDbDir)) {
    mkdir($testDbDir, 0755, true);
}
$envContent = <<<ENV
DB_DRIVER=sqlite
DB_DSN=sqlite:db/test.sqlite
SITE_URL=http://test.example.com
SITE_NAME=Test Marketplace
SESSION_SALT=test-session-salt
COOKIE_ENCRYPTION_SALT=test-cookie-salt
CSRF_SALT=test-csrf-salt
ENV;
file_put_contents($envPath, $envContent);
register_shutdown_function(static function () use ($envPath, $envBackup): void {
    if (file_exists($envBackup)) {
        copy($envBackup, $envPath);
        unlink($envBackup);
    }
});

$inc = $appDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

// Load app bootstrap first (loads Env, Db, Session, User, ApiKey and inits with app/ baseDir)
require $inc . 'bootstrap.php';
// Load remaining classes not loaded by bootstrap
require $inc . 'Config.php';
require $inc . 'Schema.php';
require $inc . 'Views.php';
require $inc . 'StatusMachine.php';
require $inc . 'api_helpers.php';

// Run schema and views on test DB (bootstrap already inited with app/ and our test .env)
$pdo = Db::pdo();
$schema = new Schema($pdo, true);
$schema->run();
$views = new Views($pdo, true);
$views->run();
$config = new Config($pdo);
$config->seedDefaults();
