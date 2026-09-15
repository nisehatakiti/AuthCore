<?php

declare(strict_types=1);

namespace AuthCore\Database;

use AuthCore\AuthCoreException;
use AuthCore\PluginMetadata;

final class ApplicationRepository
{
    public function findByKey(string $applicationKey): ?array
    {
        global $wpdb;
        $table = Schema::applications_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE application_key = %s LIMIT 1", $applicationKey), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function findById(int $applicationId): ?array
    {
        global $wpdb;
        $table = Schema::applications_table();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE application_id = %d LIMIT 1", $applicationId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function insert(PluginMetadata $metadata, ?int $parentApplicationId = null): int
    {
        global $wpdb;
        $table = Schema::applications_table();
        $now = current_time('mysql', true);

        $inserted = $wpdb->insert(
            $table,
            [
                'application_key' => $metadata->applicationKey,
                'name' => $metadata->applicationName,
                'type' => $metadata->type,
                'parent_application_id' => $parentApplicationId,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        if ($inserted === false) {
            throw new AuthCoreException('Failed to register AuthCore application: ' . (string) $wpdb->last_error);
        }

        return (int) $wpdb->insert_id;
    }

    public function updateFromMetadata(int $applicationId, PluginMetadata $metadata, ?int $parentApplicationId): void
    {
        global $wpdb;
        $table = Schema::applications_table();
        $updated = $wpdb->update(
            $table,
            [
                'name' => $metadata->applicationName,
                'type' => $metadata->type,
                'parent_application_id' => $parentApplicationId,
                'updated_at' => current_time('mysql', true),
            ],
            ['application_id' => $applicationId],
            ['%s', '%s', '%d', '%s'],
            ['%d']
        );

        if ($updated === false) {
            throw new AuthCoreException('Failed to update AuthCore application: ' . (string) $wpdb->last_error);
        }
    }
}
