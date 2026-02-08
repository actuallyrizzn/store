<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Config with real DB (SQLite path).
 * When TEST_MARIADB_DSN is set, also runs schema + Config::seedDefaults() against MariaDB (issue #23).
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

    /**
     * Run schema + views + Config::seedDefaults() on MariaDB when TEST_MARIADB_DSN is set.
     * Catches driver-specific DML bugs (e.g. INSERT OR IGNORE vs INSERT IGNORE). Skip when not set.
     */
    public function testSeedDefaultsOnMariaDBWhenAvailable(): void
    {
        $dsn = getenv('TEST_MARIADB_DSN');
        if ($dsn === false || $dsn === '') {
            $this->markTestSkipped('TEST_MARIADB_DSN not set; run with MariaDB to exercise driver path');
        }
        $pdo = new \PDO($dsn, getenv('TEST_MARIADB_USER') ?: null, getenv('TEST_MARIADB_PASS') ?: null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            $this->markTestSkipped('TEST_MARIADB_DSN did not connect as mysql/mariadb (got ' . $driver . ')');
        }
        $schema = new Schema($pdo, true);
        $schema->run();
        $views = new Views($pdo, true);
        $views->run();
        $config = new Config($pdo);
        $config->seedDefaults();
        $config->seedDefaults(); // idempotent
        $this->assertSame('24h', $config->get('pending_duration'));
        $this->assertSame(0.05, $config->getFloat('completion_tolerance'));
        $stmt = $pdo->query('SELECT COUNT(*) FROM config');
        $this->assertGreaterThanOrEqual(15, (int) $stmt->fetchColumn());
    }
}
