<?php

declare(strict_types=1);

/**
 * GET /login.php — Login form. POST /login.php — Submit login. LEMP: one script per page.
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
    if ($username === '' || $password === '') {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Missing username or password';
        return;
    }
    $user = $userRepo->verifyPassword($username, $password);
    if ($user === null) {
        http_response_code(401);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Invalid credentials';
        return;
    }
    $session->start();
    $session->setUser($user);
    $userRepo->updateLastLogin($user['uuid']);
    $redirect = trim((string) ($_POST['redirect'] ?? $_GET['redirect'] ?? ''));
    if ($redirect !== '' && $redirect[0] === '/' && strpos($redirect, '//') === false) {
        header('Location: ' . $redirect, true, 302);
        return;
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Logged in as ' . $user['username'];
    return;
}

$redirect = trim((string) ($_GET['redirect'] ?? ''));
$redirectField = $redirect !== '' && $redirect[0] === '/' && strpos($redirect, '//') === false
    ? '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirect) . '">' : '';
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><body><form method="post" action="/login.php">';
echo $redirectField;
echo 'Username: <input name="username" type="text"><br>';
echo 'Password: <input name="password" type="password"><br>';
echo '<button type="submit">Login</button></form></body></html>';
