<?php

declare(strict_types=1);

namespace AuthCore\Database;

use AuthCore\AuthCoreException;

final class UserAccountRepository
{
    public function findById(int $userAccountId, int $applicationId): ?array
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE user_account_id = %d AND application_id = %d LIMIT 1", $userAccountId, $applicationId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function findByLoginId(int $applicationId, string $loginId): ?array
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE application_id = %d AND login_id = %s LIMIT 1", $applicationId, $loginId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function findByEmail(int $applicationId, string $email): ?array
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE application_id = %d AND email = %s LIMIT 1", $applicationId, $email), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function list(int $applicationId, int $limit = 100, int $offset = 0): array
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);
        return (array) $wpdb->get_results($wpdb->prepare("SELECT user_account_id, application_id, login_id, email, status, email_verified, created_at, updated_at, last_login_at FROM {$table} WHERE application_id = %d ORDER BY user_account_id ASC LIMIT %d OFFSET %d", $applicationId, $limit, $offset), ARRAY_A);
    }

    public function insert(int $applicationId, string $loginId, string $email, string $passwordHash, string $status = 'active'): int
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $now = current_time('mysql', true);
        $inserted = $wpdb->insert($table, [
            'application_id' => $applicationId,
            'login_id' => $loginId,
            'email' => $email,
            'password_hash' => $passwordHash,
            'status' => $status,
            'email_verified' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']);
        if ($inserted === false) {
            throw new AuthCoreException('Failed to create AuthCore user account: ' . (string) $wpdb->last_error);
        }
        return (int) $wpdb->insert_id;
    }

    public function update(int $userAccountId, int $applicationId, array $data): void
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $data['updated_at'] = current_time('mysql', true);
        $formats = [];
        foreach ($data as $key => $value) {
            $formats[] = match ($key) {
                'email_verified' => '%d',
                default => '%s',
            };
        }
        $updated = $wpdb->update($table, $data, ['user_account_id' => $userAccountId, 'application_id' => $applicationId], $formats, ['%d', '%d']);
        if ($updated === false) {
            throw new AuthCoreException('Failed to update AuthCore user account: ' . (string) $wpdb->last_error);
        }
    }

    public function delete(int $userAccountId, int $applicationId): void
    {
        global $wpdb;
        $table = Schema::user_accounts_table();
        $deleted = $wpdb->delete($table, ['user_account_id' => $userAccountId, 'application_id' => $applicationId], ['%d', '%d']);
        if ($deleted === false) {
            throw new AuthCoreException('Failed to delete AuthCore user account: ' . (string) $wpdb->last_error);
        }
    }
}
