<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Db
 */
final class DbTest extends TestCase
{
    public function testPdoReturnsConnection(): void
    {
        $pdo = Db::pdo();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testDriverReturnsSqlite(): void
    {
        $driver = Db::driver();
        $this->assertSame('sqlite', strtolower($driver));
    }

    public function testIsSqliteReturnsTrueForSqlite(): void
    {
        $this->assertTrue(Db::isSqlite());
    }

    public function testInitIdempotentSecondCallDoesNotReconnect(): void
    {
        $pdo1 = Db::pdo();
        Db::init(TEST_BASE_DIR);
        $pdo2 = Db::pdo();
        $this->assertSame($pdo1, $pdo2);
    }

    public function testPdoThrowsWhenInitNotCalled(): void
    {
        $ref = new ReflectionClass(Db::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $saved = $prop->getValue();
        $prop->setValue(null, null);

        try {
            Db::pdo();
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Db::init() must be called first', $e->getMessage());
        } finally {
            $prop->setValue(null, $saved);
        }
    }

    public function testDriverThrowsWhenInitNotCalled(): void
    {
        $ref = new ReflectionClass(Db::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $saved = $prop->getValue();
        $prop->setValue(null, null);

        try {
            Db::driver();
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Db::init() must be called first', $e->getMessage());
        } finally {
            $prop->setValue(null, $saved);
        }
    }

    public function testInitThrowsForUnsupportedDriver(): void
    {
        $ref = new ReflectionClass(Db::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $saved = $prop->getValue();
        $prop->setValue(null, null);

        $envRef = new ReflectionClass(Env::class);
        $envProp = $envRef->getProperty('vars');
        $envProp->setAccessible(true);
        $envSaved = $envProp->getValue();
        $envProp->setValue(null, null);

        $tmpDir = sys_get_temp_dir() . '/db_test_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/.env', "DB_DRIVER=invalid\nDB_DSN=sqlite:db/x.sqlite\n");
        Env::load($tmpDir);

        try {
            Db::init($tmpDir);
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Unsupported DB_DRIVER', $e->getMessage());
        } finally {
            $prop->setValue(null, $saved);
            $envProp->setValue(null, $envSaved);
            Env::load(TEST_BASE_DIR);
        }
    }
}
