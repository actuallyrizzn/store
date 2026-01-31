<?php

declare(strict_types=1);

final class ItemsApiE2ETest extends E2ETestCase
{
    public function testGetItemsReturnsJson(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/items.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
    }

    public function testGetItemsWithStoreUuidFilter(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/items.php', 'get' => ['store_uuid' => 'some-uuid'], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
    }

    public function testPostItemsWithoutSessionReturns401(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'api/items.php', 'get' => [], 'post' => ['name' => 'Item', 'store_uuid' => 'x'], 'headers' => []]);
        $this->assertSame(401, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertSame('Login required', $data['error'] ?? '');
    }

    public function testInvalidMethodReturns405(): void
    {
        $res = self::runRequest(['method' => 'DELETE', 'uri' => 'api/items.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(405, $res['code']);
    }
}
