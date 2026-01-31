<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Config
 */
final class ConfigTest extends TestCase
{
    private PDO $pdo;
    private Config $config;

    protected function setUp(): void
    {
        $this->pdo = Db::pdo();
        $this->config = new Config($this->pdo);
    }

    public function testGetReturnsValueWhenKeyExists(): void
    {
        $value = $this->config->get('pending_duration');
        $this->assertNotNull($value);
        $this->assertSame('24h', $value);
    }

    public function testGetReturnsNullWhenKeyMissing(): void
    {
        $value = $this->config->get('nonexistent_key_xyz');
        $this->assertNull($value);
    }

    public function testGetUsesCacheOnSecondCall(): void
    {
        $this->config->get('pending_duration');
        $ref = new ReflectionClass($this->config);
        $prop = $ref->getProperty('cache');
        $prop->setAccessible(true);
        $cache = $prop->getValue($this->config);
        $this->assertArrayHasKey('pending_duration', $cache);
    }

    public function testGetFloatReturnsFloat(): void
    {
        $value = $this->config->getFloat('completion_tolerance');
        $this->assertIsFloat($value);
        $this->assertSame(0.05, $value);
    }

    public function testGetFloatReturnsDefaultWhenKeyMissing(): void
    {
        $value = $this->config->getFloat('nonexistent_float', 0.99);
        $this->assertSame(0.99, $value);
    }

    public function testSetUpdatesValue(): void
    {
        $this->config->set('test_key_xyz', 'test_value');
        $this->assertSame('test_value', $this->config->get('test_key_xyz'));
    }

    public function testSetUpdatesCache(): void
    {
        $this->config->set('test_cache_key', 'cached');
        $this->assertSame('cached', $this->config->get('test_cache_key'));
    }

    public function testSeedDefaultsIdempotent(): void
    {
        $this->config->seedDefaults();
        $this->config->seedDefaults();
        $value = $this->config->get('pending_duration');
        $this->assertSame('24h', $value);
    }
}
