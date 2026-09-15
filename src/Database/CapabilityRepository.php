<?php

declare(strict_types=1);

namespace AuthCore\Database;

use AuthCore\AuthCoreException;

final class CapabilityRepository
{
    public function createCapability(int $applicationId, string $capability): int
    {
        global $wpdb;
        $inserted = $wpdb->insert(Schema::capabilities_table(), ['application_id' => $applicationId, 'capability' => $capability, 'created_at' => gmdate('Y-m-d H:i:s')], ['%d', '%s', '%s']);
        if ($inserted === false) throw new AuthCoreException('Failed to create AuthCore capability.');
        return (int) $wpdb->insert_id;
    }

    public function findCapability(int $applicationId, string $capability): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Schema::capabilities_table() . ' WHERE application_id = %d AND capability = %s LIMIT 1', $applicationId, $capability), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function grant(int $applicationId, int $userAccountId, int $capabilityId): void
    {
        global $wpdb;
        $result = $wpdb->query($wpdb->prepare('INSERT IGNORE INTO ' . Schema::user_capabilities_table() . ' (application_id, user_account_id, capability_id, created_at) VALUES (%d, %d, %d, %s)', $applicationId, $userAccountId, $capabilityId, gmdate('Y-m-d H:i:s')));
        if ($result === false) throw new AuthCoreException('Failed to grant AuthCore capability.');
    }

    public function revoke(int $applicationId, int $userAccountId, int $capabilityId): void
    {
        global $wpdb;
        $result = $wpdb->delete(Schema::user_capabilities_table(), ['application_id' => $applicationId, 'user_account_id' => $userAccountId, 'capability_id' => $capabilityId], ['%d', '%d', '%d']);
        if ($result === false) throw new AuthCoreException('Failed to revoke AuthCore capability.');
    }

    public function has(int $applicationId, int $userAccountId, string $capability): bool
    {
        global $wpdb;
        $sql = 'SELECT 1 FROM ' . Schema::user_capabilities_table() . ' uc INNER JOIN ' . Schema::capabilities_table() . ' c ON c.capability_id = uc.capability_id AND c.application_id = uc.application_id WHERE uc.application_id = %d AND uc.user_account_id = %d AND c.capability = %s LIMIT 1';
        return (bool) $wpdb->get_var($wpdb->prepare($sql, $applicationId, $userAccountId, $capability));
    }
}
