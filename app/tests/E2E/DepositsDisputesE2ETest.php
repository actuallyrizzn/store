<?php

declare(strict_types=1);

final class DepositsDisputesE2ETest extends E2ETestCase
{
    public function testGetDepositsWithoutSessionReturns401(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/deposits.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testGetDisputesWithoutSessionReturns401(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/disputes.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testGetDepositsWrongMethodReturns405(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'api/deposits.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(405, $res['code']);
    }
}
