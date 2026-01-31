<?php

declare(strict_types=1);

/**
 * E2E: GET /
 */
final class IndexE2ETest extends E2ETestCase
{
    public function testGetIndexReturnsOk(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'index.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
        $this->assertSame('OK', trim($res['body']));
    }
}
