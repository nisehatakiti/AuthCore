<?php

declare(strict_types=1);

namespace AuthCore;

final class OnboardingManager
{
    public function isApplicationOnboardingRequired(int $applicationId): bool
    {
        $applicationId = (new SessionManager())->resolveApplicationContext($applicationId);
        return (new UserAccountManager())->list($applicationId, 1, 0) === [];
    }

    public function createInitialAdmin(
        int $applicationId,
        string $loginId,
        string $email,
        string $password,
        string $adminCapability = 'admin',
    ): int {
        $applicationId = (new SessionManager())->resolveApplicationContext($applicationId);
        if ((new UserAccountManager())->list($applicationId, 1, 0) !== []) {
            throw new AuthCoreException('AuthCore application onboarding has already been completed.');
        }

        $adminCapability = trim($adminCapability);
        if ($adminCapability === '' || strlen($adminCapability) > 100 || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $adminCapability) !== 1) {
            throw new AuthCoreException('Invalid administrator capability.');
        }

        $userAccountId = (new UserAccountManager())->create($applicationId, $loginId, $email, $password, 'active');
        $authorization = new AuthorizationManager();
        $authorization->registerCapability($applicationId, $adminCapability);
        $authorization->grantCapability($applicationId, $userAccountId, $adminCapability);

        return $userAccountId;
    }
}
