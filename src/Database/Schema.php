<?php

declare(strict_types=1);

namespace AuthCore\Database;

final class Schema
{
    public const APPLICATIONS_TABLE = 'authcore_applications';
    public const USER_ACCOUNTS_TABLE = 'authcore_user_accounts';
    public const VERSION = '1.1.0';

    public static function applications_table(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::APPLICATIONS_TABLE;
    }

    public static function user_accounts_table(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::USER_ACCOUNTS_TABLE;
    }
}
