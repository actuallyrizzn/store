<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \User
 */
final class UserTest extends TestCase
{
    private PDO $pdo;
    private User $userRepo;

    protected function setUp(): void
    {
        $this->pdo = Db::pdo();
        $this->userRepo = new User($this->pdo);
    }

    public function testGenerateUuidReturns32HexChars(): void
    {
        $uuid = User::generateUuid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $uuid);
        $this->assertSame(32, strlen($uuid));
    }

    public function testGenerateUuidReturnsUniqueValues(): void
    {
        $uuids = [User::generateUuid(), User::generateUuid(), User::generateUuid()];
        $this->assertCount(3, array_unique($uuids));
    }

    public function testCreateInsertsUserAndReturnsRow(): void
    {
        $uuid = User::generateUuid();
        $username = 'testuser_' . bin2hex(random_bytes(4));
        $password = 'password123';
        $row = $this->userRepo->create($uuid, $username, $password, User::ROLE_CUSTOMER, null);
        $this->assertIsArray($row);
        $this->assertSame($uuid, $row['uuid']);
        $this->assertSame($username, $row['username']);
        $this->assertSame(User::ROLE_CUSTOMER, $row['role']);
        $this->assertNotEmpty($row['passphrase_hash']);
    }

    public function testCreateWithInviter(): void
    {
        $inviterUuid = User::generateUuid();
        $this->pdo->prepare('INSERT INTO users (uuid, username, passphrase_hash, role, banned, created_at) VALUES (?, ?, ?, ?, 0, ?)')
            ->execute([$inviterUuid, 'inviter_' . random_int(1, 99999), password_hash('x', PASSWORD_BCRYPT), 'customer', date('Y-m-d H:i:s')]);
        $uuid = User::generateUuid();
        $username = 'invited_' . bin2hex(random_bytes(4));
        $row = $this->userRepo->create($uuid, $username, 'pass123', User::ROLE_CUSTOMER, $inviterUuid);
        $this->assertSame($inviterUuid, $row['inviter_uuid']);
    }

    public function testFindByUuidReturnsUser(): void
    {
        $uuid = User::generateUuid();
        $username = 'findbyuuid_' . bin2hex(random_bytes(4));
        $this->userRepo->create($uuid, $username, 'pass', User::ROLE_CUSTOMER);
        $row = $this->userRepo->findByUuid($uuid);
        $this->assertNotNull($row);
        $this->assertSame($username, $row['username']);
    }

    public function testFindByUuidReturnsNullWhenNotFound(): void
    {
        $row = $this->userRepo->findByUuid('nonexistentuuid12345678901234567890');
        $this->assertNull($row);
    }

    public function testFindByUsernameReturnsUser(): void
    {
        $uuid = User::generateUuid();
        $username = 'findbyname_' . bin2hex(random_bytes(4));
        $this->userRepo->create($uuid, $username, 'pass', User::ROLE_CUSTOMER);
        $row = $this->userRepo->findByUsername($username);
        $this->assertNotNull($row);
        $this->assertSame($uuid, $row['uuid']);
    }

    public function testFindByUsernameReturnsNullWhenNotFound(): void
    {
        $row = $this->userRepo->findByUsername('nonexistent_user_xyz_123');
        $this->assertNull($row);
    }

    public function testVerifyPasswordReturnsUserWhenCorrect(): void
    {
        $uuid = User::generateUuid();
        $username = 'verify_ok_' . bin2hex(random_bytes(4));
        $password = 'correct_password';
        $this->userRepo->create($uuid, $username, $password, User::ROLE_CUSTOMER);
        $row = $this->userRepo->verifyPassword($username, $password);
        $this->assertNotNull($row);
        $this->assertSame($username, $row['username']);
    }

    public function testVerifyPasswordReturnsNullWhenUserNotFound(): void
    {
        $row = $this->userRepo->verifyPassword('nonexistent_user_xyz', 'any');
        $this->assertNull($row);
    }

    public function testVerifyPasswordReturnsNullWhenPasswordWrong(): void
    {
        $uuid = User::generateUuid();
        $username = 'verify_wrong_' . bin2hex(random_bytes(4));
        $this->userRepo->create($uuid, $username, 'correct', User::ROLE_CUSTOMER);
        $row = $this->userRepo->verifyPassword($username, 'wrong');
        $this->assertNull($row);
    }

    public function testVerifyPasswordReturnsNullWhenBanned(): void
    {
        $uuid = User::generateUuid();
        $username = 'banned_' . bin2hex(random_bytes(4));
        $this->userRepo->create($uuid, $username, 'pass', User::ROLE_CUSTOMER);
        $this->pdo->prepare('UPDATE users SET banned = 1 WHERE uuid = ?')->execute([$uuid]);
        $row = $this->userRepo->verifyPassword($username, 'pass');
        $this->assertNull($row);
    }

    public function testUpdateLastLoginUpdatesUpdatedAt(): void
    {
        $uuid = User::generateUuid();
        $username = 'lastlogin_' . bin2hex(random_bytes(4));
        $this->userRepo->create($uuid, $username, 'pass', User::ROLE_CUSTOMER);
        $this->userRepo->updateLastLogin($uuid);
        $row = $this->userRepo->findByUuid($uuid);
        $this->assertNotNull($row['updated_at']);
    }

    public function testRoleConstants(): void
    {
        $this->assertSame('admin', User::ROLE_ADMIN);
        $this->assertSame('staff', User::ROLE_STAFF);
        $this->assertSame('vendor', User::ROLE_VENDOR);
        $this->assertSame('customer', User::ROLE_CUSTOMER);
    }
}
