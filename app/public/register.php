<?php

declare(strict_types=1);

/**
 * GET /register.php — Registration form. POST /register.php — Submit registration. LEMP: one script per page.
 */
$baseDir = dirname(__DIR__);
$inc = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
require $inc . 'Env.php';
require $inc . 'Db.php';
require $inc . 'Session.php';
require $inc . 'User.php';

Env::load($baseDir);
Db::init($baseDir);

$pdo = Db::pdo();
$session = new Session($baseDir);
$userRepo = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || strlen($username) > 16) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Invalid username';
        return;
    }
    if (strlen($password) < 8) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Password must be at least 8 characters';
        return;
    }
    if ($userRepo->findByUsername($username) !== null) {
        http_response_code(409);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Username taken';
        return;
    }
    $uuid = User::generateUuid();
    try {
        $user = $userRepo->create($uuid, $username, $password, User::ROLE_CUSTOMER, null);
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Registration failed';
        return;
    }
    if ($user !== null) {
        $session->start();
        $session->setUser($user);
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Registered as ' . $username;
    return;
}

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><body><form method="post" action="/register.php">';
echo 'Username: <input name="username" type="text"><br>';
echo 'Password: <input name="password" type="password"><br>';
echo '<button type="submit">Register</button></form></body></html>';
