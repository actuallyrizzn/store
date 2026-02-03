<?php

declare(strict_types=1);

final class AgentIdentityE2ETest extends E2ETestCase
{
    public function testAuthUserWithAgentIdentityReturnsUser(): void
    {
        $res = self::runRequest([
            'method' => 'GET',
            'uri' => 'api/auth-user.php',
            'get' => [],
            'post' => [],
            'headers' => ['X-Agent-Identity' => 'agent-e2e'],
        ]);
        $this->assertSame(200, $res['code']);
        $data = json_decode((string) ($res['body'] ?? ''), true);
        $this->assertIsArray($data);
        $this->assertSame('customer', $data['role'] ?? '');
        $this->assertNotEmpty($data['user_uuid'] ?? '');
    }
}
