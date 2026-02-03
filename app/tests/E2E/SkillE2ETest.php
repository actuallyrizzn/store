<?php

declare(strict_types=1);

final class SkillE2ETest extends E2ETestCase
{
    public function testSkillEndpointReturnsMarkdown(): void
    {
        $res = self::runRequest([
            'method' => 'GET',
            'uri' => 'api/skill.php',
            'get' => [],
            'post' => [],
            'headers' => [],
        ]);
        $this->assertSame(200, $res['code']);
        $this->assertStringContainsString('Clawed Road', $res['body'] ?? '');
        $this->assertStringContainsString('Authentication', $res['body'] ?? '');
    }
}
