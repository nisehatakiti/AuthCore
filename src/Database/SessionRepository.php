<?php

declare(strict_types=1);

namespace AuthCore\Database;

final class SessionRepository
{
    public function create(int $applicationId, int $userAccountId, string $tokenHash, string $createdAt, string $expiresAt): int
    {
        global $wpdb;

        $wpdb->insert(
            Schema::sessions_table(),
            [
                'application_id' => $applicationId,
                'user_account_id' => $userAccountId,
                'token_hash' => $tokenHash,
                'created_at' => $createdAt,
                'expires_at' => $expiresAt,
                'last_used_at' => $createdAt,
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    public function findValidByTokenHash(int $applicationId, string $tokenHash, string $now): ?array
    {
        global $wpdb;

        $table = Schema::sessions_table();
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE application_id = %d AND token_hash = %s AND expires_at > %s LIMIT 1",
                $applicationId,
                $tokenHash,
                $now
            ),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    public function touch(int $sessionId, int $applicationId, string $lastUsedAt): void
    {
        global $wpdb;

        $wpdb->update(
            Schema::sessions_table(),
            ['last_used_at' => $lastUsedAt],
            ['session_id' => $sessionId, 'application_id' => $applicationId],
            ['%s'],
            ['%d', '%d']
        );
    }

    public function deleteByTokenHash(int $applicationId, string $tokenHash): void
    {
        global $wpdb;

        $wpdb->delete(
            Schema::sessions_table(),
            ['application_id' => $applicationId, 'token_hash' => $tokenHash],
            ['%d', '%s']
        );
    }

    public function deleteExpired(string $now): void
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . Schema::sessions_table() . ' WHERE expires_at <= %s',
                $now
            )
        );
    }
}
