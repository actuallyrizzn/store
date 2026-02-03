<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Env
 */
final class EnvTest extends TestCase
{
    private string $originalCwd;

    protected function setUp(): void
    {
        $this->originalCwd = getcwd();
    }

    protected function tearDown(): void
    {
        chdir($this->originalCwd);
    }

    public function testGetReturnsValueWhenKeyExists(): void
    {
        // Env already loaded in bootstrap with TEST_BASE_DIR (app/)
        $value = Env::get('DB_DRIVER');
        $this->assertSame('sqlite', $value);
    }

    public function testGetReturnsNullWhenKeyMissing(): void
    {
        $value = Env::get('NONEXISTENT_KEY_XYZ');
        $this->assertNull($value);
    }

    public function testGetThrowsWhenLoadNotCalled(): void
    {
        // Reset Env state via reflection to test get without load
        $ref = new ReflectionClass(Env::class);
        $prop = $ref->getProperty('vars');
        $prop->setAccessible(true);
        $saved = $prop->getValue();
        $prop->setValue(null, null);

        try {
            Env::get('DB_DRIVER');
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Env::load() must be called first', $e->getMessage());
        } finally {
            $prop->setValue(null, $saved);
        }
    }

    public function testGetRequiredReturnsValueWhenPresent(): void
    {
        $value = Env::getRequired('DB_DRIVER');
        $this->assertSame('sqlite', $value);
    }

    public function testGetRequiredThrowsWhenKeyMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required env: NONEXISTENT_KEY_XYZ');
        Env::getRequired('NONEXISTENT_KEY_XYZ');
    }

    public function testGetRequiredThrowsWhenValueEmpty(): void
    {
        // Create temp .env with empty value and load in isolated way
        $tmpDir = sys_get_temp_dir() . '/env_test_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/.env', "EMPTY_KEY=\nDB_DRIVER=sqlite\n");
        $ref = new ReflectionClass(Env::class);
        $prop = $ref->getProperty('vars');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
        Env::load($tmpDir);
        try {
            Env::getRequired('EMPTY_KEY');
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Missing required env: EMPTY_KEY', $e->getMessage());
        }
        $prop->setValue(null, null);
        Env::load(TEST_BASE_DIR); // restore for other tests
        // Cleanup
        @unlink($tmpDir . '/.env');
        @rmdir($tmpDir);
    }

    public function testLoadIgnoresNonAllowedKeys(): void
    {
        // PHP_ALLOWED does not include MNEMONIC - ensure it's not loaded
        $mnemonic = Env::get('MNEMONIC');
        $this->assertNull($mnemonic);
    }

    public function testLoadIdempotentSecondCallDoesNotOverwrite(): void
    {
        $first = Env::get('DB_DRIVER');
        Env::load(TEST_BASE_DIR);
        $second = Env::get('DB_DRIVER');
        $this->assertSame($first, $second);
    }

    public function testLoadWithMissingEnvFileResultsInEmptyVars(): void
    {
        $ref = new ReflectionClass(Env::class);
        $prop = $ref->getProperty('vars');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
        $emptyDir = sys_get_temp_dir() . '/env_empty_' . getmypid();
        if (!is_dir($emptyDir)) {
            mkdir($emptyDir, 0755, true);
        }
        $this->assertFileDoesNotExist($emptyDir . DIRECTORY_SEPARATOR . '.env');
        Env::load($emptyDir);
        $this->assertNull(Env::get('DB_DRIVER'));
        $prop->setValue(null, null);
        Env::load(TEST_BASE_DIR);
    }
}
