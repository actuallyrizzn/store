<?php

declare(strict_types=1);

/**
 * Store page — show one store and its items. LEMP: one script per page. ?uuid=
 */
require_once __DIR__ . '/includes/web_bootstrap.php';

$uuid = trim((string) ($_GET['uuid'] ?? ''));
if ($uuid === '') {
    header('Location: /vendors.php');
    exit;
}

$stmt = $pdo->prepare('SELECT uuid, storename, description, created_at FROM stores WHERE uuid = ? AND deleted_at IS NULL');
$stmt->execute([$uuid]);
$store = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$store) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require_once __DIR__ . '/includes/web_header.php';
    echo '<p>Store not found.</p>';
    require_once __DIR__ . '/includes/web_footer.php';
    exit;
}

$pageTitle = $store['storename'];
$stmt = $pdo->prepare('SELECT uuid, name, description, created_at FROM items WHERE store_uuid = ? AND deleted_at IS NULL ORDER BY created_at DESC');
$stmt->execute([$uuid]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/web_header.php';
?>
<h1><?= htmlspecialchars($store['storename']) ?></h1>
<?php if (!empty($store['description'])): ?>
    <p><?= nl2br(htmlspecialchars($store['description'])) ?></p>
<?php endif; ?>
<h2>Items</h2>
<ul class="list">
    <?php foreach ($items as $row): ?>
        <li>
            <a href="/item.php?uuid=<?= urlencode($row['uuid']) ?>"><?= htmlspecialchars($row['name']) ?></a>
            <?php if (!empty($row['description'])): ?>
                <div class="meta"><?= htmlspecialchars(mb_substr($row['description'], 0, 100)) ?><?= mb_strlen($row['description']) > 100 ? '…' : '' ?></div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php if (empty($items)): ?>
    <p>No items in this store yet.</p>
<?php endif; ?>
<p><a href="/vendors.php">← Back to vendors</a></p>
<?php require_once __DIR__ . '/includes/web_footer.php';
