<?php

declare(strict_types=1);

namespace AuthCore;

final class PluginDiscovery
{
    public function __construct(private readonly ApplicationRegistry $registry = new ApplicationRegistry())
    {
    }

    public function registerActivePlugins(): void
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!function_exists('get_plugins')) {
            return;
        }

        $plugins = get_plugins();
        $active = (array) get_option('active_plugins', []);
        $networkActive = array_keys((array) get_site_option('active_sitewide_plugins', []));
        $active = array_values(array_unique(array_merge($active, $networkActive)));

        $applicationPlugins = [];
        $extensionPlugins = [];

        foreach ($active as $pluginFile) {
            if (!isset($plugins[$pluginFile])) {
                continue;
            }

            try {
                $metadata = PluginMetadata::fromFile(WP_PLUGIN_DIR . '/' . $pluginFile);
            } catch (AuthCoreException $e) {
                continue;
            }

            if ($metadata->type === 'application') {
                $applicationPlugins[] = $pluginFile;
            } else {
                $extensionPlugins[] = $pluginFile;
            }
        }

        foreach ($applicationPlugins as $pluginFile) {
            $this->register($pluginFile);
        }

        foreach ($extensionPlugins as $pluginFile) {
            $this->register($pluginFile);
        }
    }

    private function register(string $pluginFile): void
    {
        try {
            $this->registry->registerFromFile(WP_PLUGIN_DIR . '/' . $pluginFile);
        } catch (AuthCoreException $e) {
            do_action('authcore/registration_error', $e, $pluginFile);
        }
    }
}
