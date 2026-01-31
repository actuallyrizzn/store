<?php

declare(strict_types=1);

/**
 * Shared header for marketplace web pages. Expects $pageTitle (string), $currentUser (array|null).
 */
$pageTitle = $pageTitle ?? 'Clawed Road';
$currentUser = $currentUser ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — Clawed Road</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; padding: 0; line-height: 1.5; background: #f5f5f5; color: #111; }
        .header { background: #1a1a1a; color: #eee; padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
        .header a { color: #b8d4fe; text-decoration: none; }
        .header a:hover { text-decoration: underline; }
        .header .brand { font-weight: 700; margin-right: 1rem; }
        .main { max-width: 56rem; margin: 0 auto; padding: 1.5rem; background: #fff; min-height: 60vh; color: #111; }
        .list { list-style: none; padding: 0; margin: 0; }
        .list li { padding: 0.75rem; border-bottom: 1px solid #eee; }
        .list li a { color: #0066cc; text-decoration: none; }
        .list li a:hover { text-decoration: underline; }
        .meta { color: #666; font-size: 0.9rem; margin-top: 0.25rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #0052a3; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .form-group input { width: 100%; max-width: 20rem; padding: 0.5rem; }
        .alert { padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px; }
        .alert-info { background: #e7f3ff; color: #004085; }
        .alert-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
<header class="header">
    <a href="/marketplace.php" class="brand">Clawed Road</a>
    <a href="/marketplace.php">Marketplace</a>
    <a href="/vendors.php">Vendors</a>
    <?php if ($currentUser): ?>
        <a href="/payments.php">My orders</a>
        <a href="/create-store.php">Create store</a>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?><a href="/admin/index.php">Admin</a><?php endif; ?>
        <a href="/logout.php">Logout (<?= htmlspecialchars($currentUser['username']) ?>)</a>
    <?php else: ?>
        <a href="/login.php">Login</a>
        <a href="/register.php">Register</a>
    <?php endif; ?>
</header>
<main class="main">
