<?php

declare(strict_types=1);

/**
 * Bootstrap for marketplace web pages (HTML). Sets $baseDir, $pdo, $session, $userRepo, $currentUser.
 * Include from public/*.php with: require_once __DIR__ . '/includes/web_bootstrap.php';
 */
$baseDir = dirname(__DIR__, 2);
$inc = __DIR__ . DIRECTORY_SEPARATOR;
require $inc . 'Env.php';
require $inc . 'Db.php';
require $inc . 'Session.php';
require $inc . 'User.php';

Env::load($baseDir);
Db::init($baseDir);

$pdo = Db::pdo();
$session = new Session($baseDir);
$session->start();
$userRepo = new User($pdo);
$currentUser = $session->getUser();
