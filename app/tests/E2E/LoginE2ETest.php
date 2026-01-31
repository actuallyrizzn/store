<?php

declare(strict_types=1);

final class LoginE2ETest extends E2ETestCase
{
    public function testGetLoginReturnsHtmlForm(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'login.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code']);
        $this->assertStringContainsString('login.php', $res['body']);
        $this->assertStringContainsString('username', $res['body']);
        $this->assertStringContainsString('password', $res['body']);
    }

    public function testPostLoginMissingCredentialsReturns400(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'login.php', 'get' => [], 'post' => ['username' => '', 'password' => ''], 'headers' => []]);
        $this->assertSame(400, $res['code']);
        $this->assertStringContainsString('Missing', $res['body']);
    }

    public function testPostLoginInvalidCredentialsReturns401(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'login.php', 'get' => [], 'post' => ['username' => 'nonexistent_user_xyz', 'password' => 'wrong'], 'headers' => []]);
        $this->assertSame(401, $res['code']);
        $this->assertStringContainsString('Invalid', $res['body']);
    }
}
