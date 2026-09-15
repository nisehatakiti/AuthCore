<?php

declare(strict_types=1);

namespace AuthCore\Database;

use AuthCore\AuthCoreException;

final class SessionRepository
{
    public function create(int $applicationId, int $userAccountId, string $tokenHash, string $createdAt, string $expiresAt): int
    {
        global $wpdb;
        $inserted = $wpdb->insert(Schema::sessions_table(), [
            'application_id' => $applicationId,
            'user_account_id' => $userAccountId,
            'token_hash' => $tokenHash,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'last_used_at' => $createdAt,
        ], ['%d', '%d', '%s', '%s', '%s', '%s']);
        if ($inserted === false) throw new AuthCoreException('Failed to create AuthCore session.');
        return (int) $wpdb->insert_id;
    }

    public function findValidByTokenHash(int $applicationId, string $tokenHash, string $now): ?array
    {
        global $wpdb;
        $table = Schema::sessions_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE application_id = %d AND token_hash = %s AND expires_at > %s LIMIT 1", $applicationId, $tokenHash, $now), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function touch(int $sessionId, int $applicationId, string $lastUsedAt): void
    {
        global $wpdb;
        $updated = $wpdb->update(Schema::sessions_table(), ['last_used_at' => $lastUsedAt], ['session_id' => $sessionId, 'application_id' => $applicationId], ['%s'], ['%d', '%d']);
        if ($updated === false) throw new AuthCoreException('Failed to update AuthCore session.');
    }

    public function deleteByTokenHash(int $applicationId, string $tokenHash): void
    {
        global $wpdb;
        $deleted = $wpdb->delete(Schema::sessions_table(), ['application_id' => $applicationId, 'token_hash' => $tokenHash], ['%d', '%s']);
        if ($deleted === false) throw new AuthCoreException('Failed to delete AuthCore session.');
    }

    public function deleteExpired(string $now): void
    {
        global $wpdb;
        $result = $wpdb->query($wpdb->prepare('DELETE FROM ' . Schema::sessions_table() . ' WHERE expires_at <= %s', $now));
        if ($result === false) throw new AuthCoreException('Failed to purge expired AuthCore sessions.');
    }
}
