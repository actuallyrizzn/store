<?php

declare(strict_types=1);

/**
 * Run schema migrations (create tables). Usage: php app/scripts/migrate.php
 * Run from repo root so .env and data/ are found.
 */
$baseDir = dirname(__DIR__, 2);
require $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Env.php';
require $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Db.php';
require $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Schema.php';
require $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Views.php';
require $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Config.php';

Env::load($baseDir);

// Ensure SQLite data dir exists before opening DB
$dsn = Env::get('DB_DSN');
if ($dsn && strpos($dsn, 'sqlite') === 0) {
    $path = substr($dsn, 7); // "sqlite:path"
    $dir = dirname($path);
    if ($dir && !is_dir($dir)) {
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

echo "Schema, views, and config seeded.\n";
