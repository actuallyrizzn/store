<?php

declare(strict_types=1);

final class AdminE2ETest extends E2ETestCase
{
    public function testGetConfigWithoutAdminReturns403(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'admin/config.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testGetTokensWithoutAdminReturns401(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'admin/tokens.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testPostTokensRemoveWithoutAdminReturns401(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'admin/tokens-remove.php', 'get' => [], 'post' => ['id' => 1], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }
}
