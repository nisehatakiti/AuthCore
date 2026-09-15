<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Authentication\AuthenticationResult;
use AuthCore\Authentication\Authenticator;

final class AuthCore
{
    public static function version(): string { return AUTHCORE_VERSION; }
    public static function isLoaded(): bool { return did_action('authcore/loaded') > 0; }
    public static function registerPlugin(string $pluginFile): int { return (new ApplicationRegistry())->registerFromFile($pluginFile); }
    public static function getApplication(string $applicationKey): ?array { return (new ApplicationRegistry())->findByKey($applicationKey); }
    public static function createUserAccount(int $applicationId, string $loginId, string $email, string $password, string $status = 'active'): int { return (new UserAccountManager())->create($applicationId, $loginId, $email, $password, $status); }
    public static function getUserAccount(int $applicationId, int $userAccountId): ?array { return (new UserAccountManager())->get($applicationId, $userAccountId); }
    public static function listUserAccounts(int $applicationId, int $limit = 100, int $offset = 0): array { return (new UserAccountManager())->list($applicationId, $limit, $offset); }
    public static function updateUserAccount(int $applicationId, int $userAccountId, array $data): void { (new UserAccountManager())->update($applicationId, $userAccountId, $data); }
    public static function deleteUserAccount(int $applicationId, int $userAccountId): void { (new UserAccountManager())->delete($applicationId, $userAccountId); }
    public static function authenticate(int $applicationId, string $identifier, string $password): AuthenticationResult { return (new Authenticator())->authenticate($applicationId, $identifier, $password); }
    public static function login(int $applicationId, string $identifier, string $password): AuthenticationResult { return (new SessionManager())->login($applicationId, $identifier, $password); }
    public static function logout(int $applicationId): void { (new SessionManager())->logout($applicationId); }
    public static function getCurrentAccount(int $applicationId): ?array { return (new SessionManager())->getCurrentAccount($applicationId); }
    public static function registerCapability(int $applicationId, string $capability): int { return (new AuthorizationManager())->registerCapability($applicationId, $capability); }
    public static function grantCapability(int $applicationId, int $userAccountId, string $capability): void { (new AuthorizationManager())->grantCapability($applicationId, $userAccountId, $capability); }
    public static function revokeCapability(int $applicationId, int $userAccountId, string $capability): void { (new AuthorizationManager())->revokeCapability($applicationId, $userAccountId, $capability); }
    public static function hasCapability(int $applicationId, int $userAccountId, string $capability): bool { return (new AuthorizationManager())->hasCapability($applicationId, $userAccountId, $capability); }
    public static function hasCurrentUserCapability(int $applicationId, string $capability): bool { return (new AuthorizationManager())->hasCurrentUserCapability($applicationId, $capability); }
}
