<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Admin\UserManagementPage;
use AuthCore\Database\Installer;

final class Plugin
{
    public static function boot(): void
    {
        register_activation_hook(AUTHCORE_FILE, [self::class, 'activate']);
        register_deactivation_hook(AUTHCORE_FILE, [self::class, 'deactivate']);
        add_action('plugins_loaded', [self::class, 'onPluginsLoaded'], 20);
        if (is_admin()) UserManagementPage::register();
    }

    public static function activate(): void { Installer::install(); }
    public static function deactivate(): void {}

    public static function onPluginsLoaded(): void
    {
        Installer::migrate();
        do_action('authcore/loaded');
    }
}
