<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HooksTest extends TestCase
{
    public function testFireInsertsHookEvent(): void
    {
        $pdo = Db::pdo();
        $pdo->prepare('INSERT INTO hooks (event_name, webhook_url, enabled, created_at) VALUES (?, ?, ?, ?)')
            ->execute(['agent_first_request', null, 1, date('Y-m-d H:i:s')]);

        $hooks = new Hooks($pdo);
        $hooks->fire('agent_first_request', ['agent_id' => 'agent-test']);

        $stmt = $pdo->prepare('SELECT event_name, payload FROM hook_events WHERE event_name = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute(['agent_first_request']);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);
        $this->assertSame('agent_first_request', $row['event_name']);
        $this->assertStringContainsString('agent-test', $row['payload'] ?? '');
    }
}
