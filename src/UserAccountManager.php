<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Database\UserAccountRepository;

final class UserAccountManager
{
    public function __construct(private readonly UserAccountRepository $repository = new UserAccountRepository())
    {
    }

    public function create(int $applicationId, string $loginId, string $email, string $password, string $status = 'active'): int
    {
        $loginId = trim($loginId);
        $email = strtolower(trim($email));
        $this->validateLoginId($loginId);
        $this->validateEmail($email);
        $this->validatePassword($password);
        $this->validateStatus($status);

        if ($this->repository->findByLoginId($applicationId, $loginId) !== null) {
            throw new AuthCoreException('The login ID is already registered in this application.');
        }
        if ($this->repository->findByEmail($applicationId, $email) !== null) {
            throw new AuthCoreException('The email address is already registered in this application.');
        }

        return $this->repository->insert($applicationId, $loginId, $email, wp_hash_password($password), $status);
    }

    public function get(int $applicationId, int $userAccountId): ?array
    {
        return $this->repository->findById($userAccountId, $applicationId);
    }

    public function list(int $applicationId, int $limit = 100, int $offset = 0): array
    {
        return $this->repository->list($applicationId, $limit, $offset);
    }

    public function update(int $applicationId, int $userAccountId, array $data): void
    {
        $changes = [];
        if (array_key_exists('login_id', $data)) {
            $loginId = trim((string) $data['login_id']);
            $this->validateLoginId($loginId);
            $existing = $this->repository->findByLoginId($applicationId, $loginId);
            if ($existing !== null && (int) $existing['user_account_id'] !== $userAccountId) {
                throw new AuthCoreException('The login ID is already registered in this application.');
            }
            $changes['login_id'] = $loginId;
        }
        if (array_key_exists('email', $data)) {
            $email = strtolower(trim((string) $data['email']));
            $this->validateEmail($email);
            $existing = $this->repository->findByEmail($applicationId, $email);
            if ($existing !== null && (int) $existing['user_account_id'] !== $userAccountId) {
                throw new AuthCoreException('The email address is already registered in this application.');
            }
            $changes['email'] = $email;
        }
        if (array_key_exists('password', $data)) {
            $this->validatePassword((string) $data['password']);
            $changes['password_hash'] = wp_hash_password((string) $data['password']);
        }
        if (array_key_exists('status', $data)) {
            $status = (string) $data['status'];
            $this->validateStatus($status);
            $changes['status'] = $status;
        }
        if (array_key_exists('email_verified', $data)) {
            $changes['email_verified'] = (int) (bool) $data['email_verified'];
        }
        if ($changes === []) {
            return;
        }
        $this->repository->update($userAccountId, $applicationId, $changes);
    }

    public function delete(int $applicationId, int $userAccountId): void
    {
        if ($this->repository->findById($userAccountId, $applicationId) === null) {
            throw new AuthCoreException('AuthCore user account not found.');
        }
        $this->repository->delete($userAccountId, $applicationId);
    }

    private function validateLoginId(string $loginId): void
    {
        if ($loginId === '' || strlen($loginId) > 190 || !preg_match('/^[A-Za-z0-9._@+-]+$/', $loginId)) {
            throw new AuthCoreException('Invalid login ID.');
        }
    }

    private function validateEmail(string $email): void
    {
        if (strlen($email) > 190 || !is_email($email)) {
            throw new AuthCoreException('Invalid email address.');
        }
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new AuthCoreException('Password must be at least 8 characters.');
        }
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, ['active', 'suspended', 'disabled'], true)) {
            throw new AuthCoreException('Invalid account status.');
        }
    }
}
