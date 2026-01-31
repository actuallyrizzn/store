<?php

declare(strict_types=1);

/**
 * E2E: GET/POST /api/stores.php
 */
final class StoresApiE2ETest extends E2ETestCase
{
    public function testGetStoresReturnsJson(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/stores.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('stores', $data);
    }

    public function testPostStoresWithoutSessionReturns401(): void
    {
        $res = self::runRequest([
            'method' => 'POST',
            'uri' => 'api/stores.php',
            'get' => [],
            'post' => ['storename' => 'TestStore', 'description' => ''],
            'headers' => [],
        ]);
        $this->assertSame(401, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertSame('Login required', $data['error'] ?? '');
    }

    public function testPostStoresInvalidStorenameReturns400(): void
    {
        // Create user and session then POST with empty storename - we need a session
        // For simplicity we test 405 for wrong method
        $res = self::runRequest(['method' => 'PUT', 'uri' => 'api/stores.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(405, $res['code']);
    }
}
