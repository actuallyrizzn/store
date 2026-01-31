<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Config with real DB (SQLite path).
 */
final class ConfigIntegrationTest extends TestCase
{
    private PDO $pdo;
    private Config $config;

    protected function setUp(): void
    {
        $this->pdo = Db::pdo();
        $this->config = new Config($this->pdo);
    }

    public function testSeedDefaultsInsertsAllKeys(): void
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM config');
        $count = (int) $stmt->fetchColumn();
        $this->assertGreaterThanOrEqual(15, $count);
    }

    public function testGetSetRoundtrip(): void
    {
        $this->config->set('integration_test_key', 'integration_value');
        $this->assertSame('integration_value', $this->config->get('integration_test_key'));
    }
}
