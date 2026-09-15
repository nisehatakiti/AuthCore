<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Database\CapabilityRepository;

final class AuthorizationManager
{
    public function __construct(private readonly CapabilityRepository $repository = new CapabilityRepository()) {}

    public function registerCapability(int $applicationId, string $capability): int
    {
        $capability = trim($capability);
        $this->validateCapability($capability);
        $contextId = $this->resolveApplicationContext($applicationId);
        $existing = $this->repository->findCapability($contextId, $capability);
        return $existing ? (int) $existing['capability_id'] : $this->repository->createCapability($contextId, $capability);
    }

    public function grantCapability(int $applicationId, int $userAccountId, string $capability): void
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        $this->assertAccountInContext($contextId, $userAccountId);
        $definition = $this->repository->findCapability($contextId, trim($capability));
        if ($definition === null) {
            throw new AuthCoreException('Capability is not registered.');
        }
        $this->repository->grant($contextId, $userAccountId, (int) $definition['capability_id']);
    }

    public function revokeCapability(int $applicationId, int $userAccountId, string $capability): void
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        $definition = $this->repository->findCapability($contextId, trim($capability));
        if ($definition !== null) {
            $this->repository->revoke($contextId, $userAccountId, (int) $definition['capability_id']);
        }
    }

    public function hasCapability(int $applicationId, int $userAccountId, string $capability): bool
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        if (!$this->accountExists($contextId, $userAccountId)) return false;
        $this->validateCapability(trim($capability));
        return $this->repository->has($contextId, $userAccountId, trim($capability));
    }

    public function hasCurrentUserCapability(int $applicationId, string $capability): bool
    {
        $account = (new SessionManager())->getCurrentAccount($applicationId);
        return is_array($account) && isset($account['user_account_id']) && $this->hasCapability($applicationId, (int) $account['user_account_id'], $capability);
    }

    private function resolveApplicationContext(int $applicationId): int
    {
        return (new SessionManager())->resolveApplicationContext($applicationId);
    }

    private function accountExists(int $applicationId, int $userAccountId): bool
    {
        return (new UserAccountManager())->get($applicationId, $userAccountId) !== null;
    }

    private function assertAccountInContext(int $applicationId, int $userAccountId): void
    {
        if (!$this->accountExists($applicationId, $userAccountId)) throw new AuthCoreException('AuthCore user account not found.');
    }

    private function validateCapability(string $capability): void
    {
        if ($capability === '' || strlen($capability) > 100 || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $capability) !== 1) {
            throw new AuthCoreException('Invalid capability.');
        }
    }
}
