<?php

declare(strict_types=1);

/**
 * Payment — show one transaction (order). Session required; buyer or vendor. LEMP: one script per page. ?uuid=
 */
require_once __DIR__ . '/includes/web_bootstrap.php';

$uuid = trim((string) ($_GET['uuid'] ?? ''));
if ($uuid === '') {
    header('Location: /payments.php');
    exit;
}

if (!$currentUser) {
    header('Location: /login.php?redirect=' . urlencode('/payment.php?uuid=' . $uuid));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM v_current_cumulative_transaction_statuses WHERE uuid = ?');
$stmt->execute([$uuid]);
$tx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tx) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require_once __DIR__ . '/includes/web_header.php';
    echo '<p>Order not found.</p>';
    require_once __DIR__ . '/includes/web_footer.php';
    exit;
}

$isBuyer = ($tx['buyer_uuid'] ?? '') === $currentUser['uuid'];
$isVendor = false;
if (!$isBuyer) {
    $check = $pdo->prepare('SELECT 1 FROM store_users WHERE store_uuid = ? AND user_uuid = ?');
    $check->execute([$tx['store_uuid'] ?? '', $currentUser['uuid']]);
    $isVendor = (bool) $check->fetch();
}
if (!$isBuyer && !$isVendor && ($currentUser['role'] ?? '') !== 'admin') {
    http_response_code(403);
    $pageTitle = 'Forbidden';
    require_once __DIR__ . '/includes/web_header.php';
    echo '<p>You do not have access to this order.</p>';
    require_once __DIR__ . '/includes/web_footer.php';
    exit;
}

$pageTitle = 'Order ' . substr($uuid, 0, 8) . '…';

require_once __DIR__ . '/includes/web_header.php';
?>
<h1>Order</h1>
<dl style="display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 1rem;">
    <dt>UUID</dt><dd><code><?= htmlspecialchars($uuid) ?></code></dd>
    <dt>Status</dt><dd><?= htmlspecialchars($tx['current_status'] ?? '—') ?></dd>
    <dt>Updated</dt><dd><?= htmlspecialchars($tx['updated_at'] ?? '—') ?></dd>
    <?php if (!empty($tx['escrow_address'])): ?>
        <dt>Escrow address</dt><dd><code><?= htmlspecialchars($tx['escrow_address']) ?></code></dd>
        <dt></dt><dd class="alert alert-info">Send payment to this address. Amount and currency are set when the order was created.</dd>
    <?php else: ?>
        <dt>Escrow</dt><dd class="alert alert-warning">Escrow address pending (cron will fill it shortly).</dd>
    <?php endif; ?>
</dl>
<p><a href="/payments.php">← My orders</a></p>
<?php require_once __DIR__ . '/includes/web_footer.php';
