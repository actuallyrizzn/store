<?php

declare(strict_types=1);

/**
 * GET /login.php — Login form. POST /login.php — Submit login. Rate limit 10 attempts per 5 min per IP.
 */
require_once __DIR__ . '/includes/web_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '0');
    $cutoff = date('Y-m-d H:i:s', time() - 300);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_rate_limit WHERE ip_hash = ? AND attempted_at > ?');
    $stmt->execute([$ipHash, $cutoff]);
    if ((int) $stmt->fetchColumn() >= 10) {
        $pageTitle = 'Login';
        $redirectParam = trim((string) ($_POST['redirect'] ?? ''));
        $redirectParam = ($redirectParam !== '' && $redirectParam[0] === '/' && strpos($redirectParam, '//') === false) ? $redirectParam : '';
        require_once __DIR__ . '/includes/web_header.php';
        echo '<p class="alert alert-warning">Too many login attempts. Please try again in a few minutes.</p>';
        include __DIR__ . '/includes/form_login.php';
        require_once __DIR__ . '/includes/web_footer.php';
        return;
    }
    $pdo->prepare('INSERT INTO login_rate_limit (ip_hash, attempted_at) VALUES (?, ?)')->execute([$ipHash, date('Y-m-d H:i:s')]);

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $pageTitle = 'Login';
        $redirectParam = trim((string) ($_POST['redirect'] ?? ''));
        $redirectParam = ($redirectParam !== '' && $redirectParam[0] === '/' && strpos($redirectParam, '//') === false) ? $redirectParam : '';
        require_once __DIR__ . '/includes/web_header.php';
        echo '<p class="alert alert-warning">Missing username or password.</p>';
        include __DIR__ . '/includes/form_login.php';
        require_once __DIR__ . '/includes/web_footer.php';
        return;
    }
    $user = $userRepo->verifyPassword($username, $password);
    if ($user === null) {
        $pageTitle = 'Login';
        $redirectParam = trim((string) ($_POST['redirect'] ?? ''));
        $redirectParam = ($redirectParam !== '' && $redirectParam[0] === '/' && strpos($redirectParam, '//') === false) ? $redirectParam : '';
        require_once __DIR__ . '/includes/web_header.php';
        echo '<p class="alert alert-warning">Invalid credentials.</p>';
        include __DIR__ . '/includes/form_login.php';
        require_once __DIR__ . '/includes/web_footer.php';
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
    header('Location: /marketplace.php', true, 302);
    return;
}

$pageTitle = 'Login';
$redirect = trim((string) ($_GET['redirect'] ?? ''));
$redirectParam = ($redirect !== '' && $redirect[0] === '/' && strpos($redirect, '//') === false) ? $redirect : '';
require_once __DIR__ . '/includes/web_header.php';
include __DIR__ . '/includes/form_login.php';
require_once __DIR__ . '/includes/web_footer.php';
