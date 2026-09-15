<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Database\Installer;

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
        Installer::install();
    }

    public static function deactivate(): void
    {
        // AuthCore does not remove data on deactivation.
    }

    public static function initialize(): void
    {
        self::maybeMigrate();
        do_action('authcore/loaded', AUTHCORE_VERSION);
    }

    private static function maybeMigrate(): void
    {
        $installed_version = get_option(Config::OPTION_DB_VERSION, null);

        if ($installed_version !== Config::DB_VERSION) {
            Installer::migrate();
        }
    }
}
