<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Schema
 */
final class SchemaTest extends TestCase
{
    public function testRunCreatesAllTables(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $schema = new Schema($pdo, true);
        $schema->run();

        $tables = ['users', 'stores', 'store_users', 'items', 'item_categories', 'packages', 'package_prices',
            'transactions', 'evm_transactions', 'transaction_statuses', 'transaction_intents', 'shipping_statuses',
            'payment_receipts', 'referral_payments', 'deposits', 'deposit_history', 'disputes', 'dispute_claims',
            'registration_rate_limit', 'agent_identities', 'agent_requests', 'hooks', 'hook_events',
            'config', 'api_keys', 'api_key_requests', 'accepted_tokens'];

        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name=" . $pdo->quote($table));
            $this->assertNotFalse($stmt->fetch(), "Table $table should exist");
        }
    }

    public function testRunIdempotent(): void
    {
        $pdo = Db::pdo();
        $schema = new Schema($pdo, true);
        $schema->run();
        $schema->run();
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        $this->assertNotFalse($stmt->fetch());
    }
}
