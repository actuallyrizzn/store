<?php

declare(strict_types=1);

final class TransactionsApiE2ETest extends E2ETestCase
{
    public function testGetTransactionsWithoutAuthReturns401(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/transactions.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testGetAuthUserWithoutKeyReturns401(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'api/auth-user.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testGetAuthUserWithInvalidKeyReturns401(): void
    {
        $res = self::runRequest([
            'method' => 'GET',
            'uri' => 'api/auth-user.php',
            'get' => [],
            'post' => [],
            'headers' => ['Authorization' => 'Bearer invalid-key-xyz'],
        ]);
        $this->assertSame(401, $res['code']);
    }

    public function testPostTransactionsWithoutSessionReturns401(): void
    {
        $res = self::runRequest(['method' => 'POST', 'uri' => 'api/transactions.php', 'get' => [], 'post' => ['package_uuid' => 'x', 'required_amount' => 0.1], 'headers' => []]);
        $this->assertSame(401, $res['code']);
    }

    public function testPostTransactionsWithSessionButWithoutCsrfReturns403(): void
    {
        $cookies = self::loginAs('e2e_customer', 'password123');
        $this->assertNotEmpty($cookies, 'Login should succeed');

        $res = self::runRequest([
            'method' => 'POST',
            'uri' => 'api/transactions.php',
            'get' => [],
            'post' => ['package_uuid' => 'fake-package', 'required_amount' => 0.1],
            'headers' => [],
            'cookies' => $cookies,
        ]);
        $this->assertSame(403, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertSame('CSRF token required', $data['error'] ?? '');
    }

    /**
     * E2E: POST /api/transactions.php with session + CSRF + valid package hits User::generateUuid() path (issue #22).
     */
    public function testPostTransactionsWithSessionAndCsrfAndValidPackageSucceeds(): void
    {
        $this->assertTrue(defined('E2E_PACKAGE_UUID'), 'Bootstrap must seed E2E_PACKAGE_UUID');
        $packageUuid = E2E_PACKAGE_UUID;

        $cookies = self::loginAs('e2e_customer', 'password123');
        $this->assertNotEmpty($cookies, 'Login should succeed');

        $pageRes = self::runRequest([
            'method' => 'GET',
            'uri' => 'register.php',
            'get' => [],
            'post' => [],
            'headers' => [],
            'cookies' => $cookies,
        ]);
        $csrf = self::extractCsrfFromBody($pageRes['body'] ?? '');
        $this->assertNotSame('', $csrf, 'Should extract CSRF token');

        $res = self::runRequest([
            'method' => 'POST',
            'uri' => 'api/transactions.php',
            'get' => [],
            'post' => [
                'package_uuid' => $packageUuid,
                'required_amount' => 0.1,
                'chain_id' => 1,
                'currency' => 'ETH',
                'csrf_token' => $csrf,
            ],
            'headers' => [],
            'cookies' => $cookies,
        ]);
        $this->assertSame(200, $res['code']);
        $data = json_decode($res['body'], true);
        $this->assertTrue($data['ok'] ?? false);
        $this->assertNotEmpty($data['uuid'] ?? '');
        $this->assertTrue($data['escrow_address_pending'] ?? false);
    }
}
