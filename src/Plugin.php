<?php

declare(strict_types=1);

namespace AuthCore;

final class Plugin
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        register_activation_hook(AUTHCORE_FILE, [self::class, 'activate']);
        register_deactivation_hook(AUTHCORE_FILE, [self::class, 'deactivate']);

        add_action('plugins_loaded', [self::class, 'initialize'], 20);
    }

    public static function activate(): void
    {
        if (get_option(Config::OPTION_DB_VERSION, null) === null) {
            add_option(Config::OPTION_DB_VERSION, Config::DB_VERSION, '', false);
        }
    }

    public static function deactivate(): void
    {
        // Phase 0 intentionally performs no destructive cleanup on deactivation.
    }

    public static function initialize(): void
    {
        do_action('authcore/loaded', AUTHCORE_VERSION);
    }
}
