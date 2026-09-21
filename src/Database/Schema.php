<?php

declare(strict_types=1);

namespace AuthCore\Database;

final class Schema
{
    public const APPLICATIONS_TABLE = 'authcore_applications';
    public const USER_ACCOUNTS_TABLE = 'authcore_user_accounts';
    public const SESSIONS_TABLE = 'authcore_sessions';
    public const CAPABILITIES_TABLE = 'authcore_capabilities';
    public const USER_CAPABILITIES_TABLE = 'authcore_user_capabilities';
    public const VERSION = '1.3.0';

    public static function version(): string { return self::VERSION; }

    public static function applications_table(): string { global $wpdb; return $wpdb->prefix . self::APPLICATIONS_TABLE; }
    public static function user_accounts_table(): string { global $wpdb; return $wpdb->prefix . self::USER_ACCOUNTS_TABLE; }
    public static function sessions_table(): string { global $wpdb; return $wpdb->prefix . self::SESSIONS_TABLE; }
    public static function capabilities_table(): string { global $wpdb; return $wpdb->prefix . self::CAPABILITIES_TABLE; }
    public static function user_capabilities_table(): string { global $wpdb; return $wpdb->prefix . self::USER_CAPABILITIES_TABLE; }
}
