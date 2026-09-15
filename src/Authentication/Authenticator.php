<?php

declare(strict_types=1);

namespace AuthCore\Authentication;

use AuthCore\AuthCoreException;
use AuthCore\Database\UserAccountRepository;

final class Authenticator
{
    public function __construct(private readonly UserAccountRepository $repository = new UserAccountRepository()) {}

    public function authenticate(int $applicationId, string $identifier, string $password): AuthenticationResult
    {
        if (strlen($password) > 4096) return AuthenticationResult::failure();
        $identifier = trim($identifier);
        if ($identifier === '' || strlen($identifier) > 190) return AuthenticationResult::failure();
        $account = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->repository->findByEmail($applicationId, strtolower($identifier))
            : $this->repository->findByLoginId($applicationId, $identifier);

        // Always perform a password hash check so a missing account does not
        // become distinguishable from a wrong password by response timing.
        $hash = is_array($account) && isset($account['password_hash'])
            ? (string) $account['password_hash']
            : '$P$invalid-authcore-dummy-hash';
        $passwordValid = wp_check_password($password, $hash);

        if (!is_array($account) || ($account['status'] ?? '') !== 'active' || !$passwordValid) return AuthenticationResult::failure();

        if (isset($account['user_account_id'])) {
            global $wpdb;
            $table = \AuthCore\Database\Schema::user_accounts_table();
            $now = current_time('mysql', true);
            $updated = $wpdb->update($table, ['last_login_at' => $now], ['user_account_id' => (int) $account['user_account_id'], 'application_id' => $applicationId], ['%s'], ['%d', '%d']);
            if ($updated === false) throw new AuthCoreException('Failed to update AuthCore login state.');
            $account['last_login_at'] = $now;
        }

        unset($account['password_hash']);
        return AuthenticationResult::success($account);
    }
}
