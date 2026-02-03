<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AgentIdentityTest extends TestCase
{
    public function testVerifyTokenInTestModeReturnsProfile(): void
    {
        $pdo = Db::pdo();
        $userRepo = new User($pdo);
        $agentIdentity = new AgentIdentity($pdo, $userRepo);
        $profile = $agentIdentity->verifyToken('agent-test');
        $this->assertIsArray($profile);
        $this->assertSame('agent-test', $profile['id']);
        $this->assertSame('Test Agent', $profile['name']);
    }

    public function testGetOrCreateUserCreatesAndReuses(): void
    {
        $pdo = Db::pdo();
        $userRepo = new User($pdo);
        $agentIdentity = new AgentIdentity($pdo, $userRepo);
        $agentId = 'agent-' . bin2hex(random_bytes(4));
        $result = $agentIdentity->getOrCreateUser($agentId, 'Agent One');
        $this->assertIsArray($result);
        $this->assertTrue($result['is_new']);
        $this->assertSame('customer', $result['user']['role']);

        $result2 = $agentIdentity->getOrCreateUser($agentId, 'Agent One');
        $this->assertIsArray($result2);
        $this->assertFalse($result2['is_new']);
        $this->assertSame($result['user']['uuid'], $result2['user']['uuid']);
    }
}
