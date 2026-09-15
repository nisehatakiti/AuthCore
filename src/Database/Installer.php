<?php

declare(strict_types=1);

namespace AuthCore\Database;

use AuthCore\Config;

final class Installer
{
    public static function install(): void { self::migrate(); }

    public static function migrate(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();
        $applications = Schema::applications_table();
        $user_accounts = Schema::user_accounts_table();
        $sessions = Schema::sessions_table();
        $capabilities = Schema::capabilities_table();
        $user_capabilities = Schema::user_capabilities_table();

        $sql = "CREATE TABLE {$applications} (
            application_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            application_key varchar(100) NOT NULL,
            name varchar(190) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'application',
            parent_application_id bigint(20) unsigned NULL DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (application_id), UNIQUE KEY application_key (application_key), KEY type (type), KEY parent_application_id (parent_application_id), KEY status (status)
        ) {$charset_collate};
        CREATE TABLE {$user_accounts} (
            user_account_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            application_id bigint(20) unsigned NOT NULL,
            login_id varchar(190) NOT NULL, email varchar(190) NOT NULL, password_hash varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active', email_verified tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL, updated_at datetime NOT NULL, last_login_at datetime NULL DEFAULT NULL,
            PRIMARY KEY (user_account_id), UNIQUE KEY application_login (application_id, login_id), UNIQUE KEY application_email (application_id, email), KEY application_id (application_id), KEY status (status)
        ) {$charset_collate};
        CREATE TABLE {$sessions} (
            session_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, application_id bigint(20) unsigned NOT NULL,
            user_account_id bigint(20) unsigned NOT NULL, token_hash char(64) NOT NULL,
            created_at datetime NOT NULL, expires_at datetime NOT NULL, last_used_at datetime NOT NULL,
            PRIMARY KEY (session_id), UNIQUE KEY token_hash (token_hash), KEY application_id (application_id), KEY user_account_id (user_account_id), KEY expires_at (expires_at)
        ) {$charset_collate};
        CREATE TABLE {$capabilities} (
            capability_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, application_id bigint(20) unsigned NOT NULL,
            capability varchar(100) NOT NULL, created_at datetime NOT NULL,
            PRIMARY KEY (capability_id), UNIQUE KEY application_capability (application_id, capability), KEY application_id (application_id)
        ) {$charset_collate};
        CREATE TABLE {$user_capabilities} (
            user_capability_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, application_id bigint(20) unsigned NOT NULL,
            user_account_id bigint(20) unsigned NOT NULL, capability_id bigint(20) unsigned NOT NULL, created_at datetime NOT NULL,
            PRIMARY KEY (user_capability_id), UNIQUE KEY user_capability (application_id, user_account_id, capability_id), KEY application_id (application_id), KEY user_account_id (user_account_id), KEY capability_id (capability_id)
        ) {$charset_collate};";
        dbDelta($sql);
        update_option(Config::OPTION_DB_VERSION, Schema::version(), false);
    }
}
