<?php

declare(strict_types=1);

final class LogoutE2ETest extends E2ETestCase
{
    public function testGetLogoutReturnsLoggedOut(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'logout.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
        $this->assertStringContainsString('Logged out', $res['body']);
    }
}
