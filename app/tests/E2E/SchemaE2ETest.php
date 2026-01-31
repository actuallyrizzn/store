<?php

declare(strict_types=1);

final class SchemaE2ETest extends E2ETestCase
{
    public function testGetSchemaReturnsSuccess(): void
    {
        $res = self::runRequest(['method' => 'GET', 'uri' => 'schema.php', 'get' => [], 'post' => [], 'headers' => []]);
        $this->assertSame(200, $res['code'], 'Schema response: ' . ($res['body'] ?? 'empty'));
        $this->assertNotEmpty($res['body']);
        // When run via CLI (run_request.php) schema echoes plain text; via HTTP it returns JSON
        $data = json_decode($res['body'], true);
        if (is_array($data)) {
            $this->assertTrue($data['ok'] ?? false);
        } else {
            $this->assertStringContainsString('Schema', $res['body']);
        }
    }
}
