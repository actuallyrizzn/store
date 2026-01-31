<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests getApiKeyFromRequest (defined in bootstrap.php).
 */
final class BootstrapTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['HTTP_X_API_KEY'], $_GET['token']);
    }

    public function testGetApiKeyFromRequestBearer(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer my-api-key-123';
        $key = getApiKeyFromRequest();
        $this->assertSame('my-api-key-123', $key);
    }

    public function testGetApiKeyFromRequestBearerCaseInsensitive(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'bearer my-key';
        $key = getApiKeyFromRequest();
        $this->assertSame('my-key', $key);
    }

    public function testGetApiKeyFromRequestBearerTrimsWhitespace(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = '  Bearer   trimmed-key  ';
        $key = getApiKeyFromRequest();
        $this->assertSame('trimmed-key', $key);
    }

    public function testGetApiKeyFromRequestXApiKey(): void
    {
        $_SERVER['HTTP_X_API_KEY'] = 'x-api-key-value';
        $key = getApiKeyFromRequest();
        $this->assertSame('x-api-key-value', $key);
    }

    public function testGetApiKeyFromRequestQueryToken(): void
    {
        $_GET['token'] = 'query-token';
        $key = getApiKeyFromRequest();
        $this->assertSame('query-token', $key);
    }

    public function testGetApiKeyFromRequestReturnsNullWhenMissing(): void
    {
        $key = getApiKeyFromRequest();
        $this->assertNull($key);
    }

    public function testGetApiKeyFromRequestEmptyXApiKeyFallsThrough(): void
    {
        $_SERVER['HTTP_X_API_KEY'] = '';
        $_GET['token'] = 'fallback';
        $key = getApiKeyFromRequest();
        $this->assertSame('fallback', $key);
    }
}
