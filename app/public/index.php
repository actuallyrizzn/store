<?php

declare(strict_types=1);

/**
 * GET / — Redirect to marketplace (like v1 Index → /marketplace). LEMP: document root.
 */
header('Location: /marketplace.php', true, 302);
exit;
