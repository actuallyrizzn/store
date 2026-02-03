<?php

declare(strict_types=1);

/**
 * Agent identity verification and mapping.
 * Verifies an opaque token via external endpoint and maps to a local user.
 */
final class AgentIdentity
{
    private \PDO $pdo;
    private User $userRepo;

    public function __construct(\PDO $pdo, User $userRepo)
    {
        $this->pdo = $pdo;
        $this->userRepo = $userRepo;
    }

    /**
     * Verify token with external provider.
     * Returns ['id' => string, 'name' => string] or null on failure.
     */
    public function verifyToken(string $token): ?array
    {
        $verifyUrl = Env::get('AGENT_IDENTITY_VERIFY_URL') ?? '';
        if ($verifyUrl === 'test') {
            if ($token === '') {
                return null;
            }
            return ['id' => $token, 'name' => 'Test Agent'];
        }
        if ($verifyUrl === '') {
            return null;
        }
        $payload = json_encode(['token' => $token]);
        if ($payload === false) {
            return null;
        }
        $headers = ['Content-Type: application/json'];
        $appKey = Env::get('AGENT_IDENTITY_APP_KEY') ?? '';
        if ($appKey !== '') {
            $headers[] = 'X-Agent-Identity-App-Key: ' . $appKey;
        }
        $ch = curl_init($verifyUrl);
        if ($ch === false) {
            return null;
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false || $status < 200 || $status >= 300) {
            return null;
        }
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['id']) || empty($data['name'])) {
            return null;
        }
        return [
            'id' => (string) $data['id'],
            'name' => (string) $data['name'],
        ];
    }

    /**
     * Get or create a local user for a verified agent.
     * Returns ['user' => array, 'is_new' => bool] or null on failure.
     */
    public function getOrCreateUser(string $agentId, string $agentName, string $provider = 'external'): ?array
    {
        $stmt = $this->pdo->prepare('SELECT user_uuid FROM agent_identities WHERE agent_id = ? LIMIT 1');
        $stmt->execute([$agentId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $now = date('Y-m-d H:i:s');
        if ($row && !empty($row['user_uuid'])) {
            $this->pdo->prepare('UPDATE agent_identities SET agent_name = ?, last_verified_at = ? WHERE agent_id = ?')
                ->execute([$agentName, $now, $agentId]);
            $user = $this->userRepo->findByUuid((string) $row['user_uuid']);
            return $user === null ? null : ['user' => $user, 'is_new' => false];
        }

        $uuid = User::generateUuid();
        $username = 'agent_' . substr(hash('sha256', $provider . ':' . $agentId), 0, 8);
        $password = bin2hex(random_bytes(16));
        $user = $this->userRepo->create($uuid, $username, $password, User::ROLE_CUSTOMER, null);
        if ($user === null) {
            return null;
        }
        $this->pdo->prepare('INSERT INTO agent_identities (agent_id, agent_name, provider, user_uuid, first_verified_at, last_verified_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$agentId, $agentName, $provider, $uuid, $now, $now]);
        return ['user' => $user, 'is_new' => true];
    }
}
