<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Views
 */
final class ViewsTest extends TestCase
{
    public function testRunCreatesAllViews(): void
    {
        $pdo = Db::pdo();
        $views = new Views($pdo, true);
        $views->run();

        $viewNames = ['v_transaction_statuses', 'v_shipping_statuses', 'v_current_transaction_statuses',
            'v_current_evm_transaction_statuses', 'v_current_cumulative_transaction_statuses'];

        foreach ($viewNames as $view) {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='view' AND name=" . $pdo->quote($view));
            $this->assertNotFalse($stmt->fetch(), "View $view should exist");
        }
    }

    public function testRunDropViewsThenCreate(): void
    {
        $pdo = Db::pdo();
        $views = new Views($pdo, true);
        $views->run();
        $views->run();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='view' AND name='v_transaction_statuses'");
        $this->assertNotFalse($stmt->fetch());
    }
}
