<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \Session
 */
final class SessionTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session(TEST_BASE_DIR);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            $_SESSION = [];
        }
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            $_SESSION = [];
        }
    }

    public function testGetUserReturnsNullWhenNotSet(): void
    {
        @session_start();
        $_SESSION = [];
        $user = $this->session->getUser();
        session_destroy();
        $this->assertNull($user);
    }

    public function testSetUserAndGetUserRoundtrip(): void
    {
        @session_start();
        $_SESSION = [];
        $userData = ['uuid' => 'user123', 'username' => 'testuser', 'role' => 'customer'];
        $this->session->setUser($userData);
        $user = $this->session->getUser();
        session_destroy();
        $this->assertNotNull($user);
        $this->assertSame('user123', $user['uuid']);
        $this->assertSame('testuser', $user['username']);
        $this->assertSame('customer', $user['role']);
    }

    public function testGetUserReturnsDefaultsForMissingKeys(): void
    {
        @session_start();
        $_SESSION = ['user_uuid' => 'u1'];
        $user = $this->session->getUser();
        session_destroy();
        $this->assertNotNull($user);
        $this->assertSame('', $user['username']);
        $this->assertSame('customer', $user['role']);
    }

    public function testStartSetsSessionName(): void
    {
        $this->session->start();
        $name = session_name();
        $this->assertStringStartsWith('store_', $name);
        $this->assertSame(22, strlen($name)); // store_ + 16 hex
    }

    public function testStartIdempotent(): void
    {
        $this->session->start();
        $id1 = session_id();
        $this->session->start();
        $id2 = session_id();
        $this->assertSame($id1, $id2);
    }

    public function testDestroyClearsSession(): void
    {
        $this->session->start();
        $this->session->setUser(['uuid' => 'x', 'username' => 'y', 'role' => 'customer']);
        $this->session->destroy();
        @session_start();
        $this->assertEmpty($_SESSION);
        session_destroy();
    }
}
