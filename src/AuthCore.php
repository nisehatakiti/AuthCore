<?php

declare(strict_types=1);

namespace AuthCore;

final class AuthCore
{
    public static function version(): string
    {
        return AUTHCORE_VERSION;
    }

    public static function isLoaded(): bool
    {
        return did_action('authcore/loaded') > 0;
    }

    public static function registerPlugin(string $pluginFile): int
    {
        return (new ApplicationRegistry())->registerFromFile($pluginFile);
    }

    public static function getApplication(string $applicationKey): ?array
    {
        return (new ApplicationRegistry())->findByKey($applicationKey);
    }

    public static function createUserAccount(int $applicationId, string $loginId, string $email, string $password, string $status = 'active'): int
    {
        return (new UserAccountManager())->create($applicationId, $loginId, $email, $password, $status);
    }

    public static function getUserAccount(int $applicationId, int $userAccountId): ?array
    {
        return (new UserAccountManager())->get($applicationId, $userAccountId);
    }

    public static function listUserAccounts(int $applicationId, int $limit = 100, int $offset = 0): array
    {
        return (new UserAccountManager())->list($applicationId, $limit, $offset);
    }

    public static function updateUserAccount(int $applicationId, int $userAccountId, array $data): void
    {
        (new UserAccountManager())->update($applicationId, $userAccountId, $data);
    }

    public static function deleteUserAccount(int $applicationId, int $userAccountId): void
    {
        (new UserAccountManager())->delete($applicationId, $userAccountId);
    }
}
