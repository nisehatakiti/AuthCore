<?php

declare(strict_types=1);

namespace AuthCore\Admin;

use AuthCore\AuthCore;

final class UserManagementPage
{
    /** @var array<string, UserManagementContext> */
    private static array $contexts = [];

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
    }

    public static function registerContext(
        string $applicationKey,
        string $parentMenuSlug,
        string $menuTitle = 'Users',
        string $pageTitle = 'Users',
        string $capability = 'manage_options',
    ): string {
        $context = UserManagementContext::create($applicationKey, $parentMenuSlug, $menuTitle, $pageTitle, $capability);
        self::$contexts[$context->menuSlug] = $context;
        return $context->menuSlug;
    }

    public static function menu(): void
    {
        foreach (self::$contexts as $context) {
            add_submenu_page(
                $context->parentMenuSlug,
                $context->pageTitle,
                $context->menuTitle,
                $context->capability,
                $context->menuSlug,
                static function () use ($context): void {
                    self::renderContext($context);
                }
            );
        }
    }

    private static function renderContext(UserManagementContext $context): void
    {
        if (!current_user_can($context->capability)) {
            wp_die(esc_html__('You do not have permission to access this page.', 'authcore'));
        }

        $users = AuthCore::listUserAccounts($context->applicationId, 100, 0);
        $application = AuthCore::getApplication($context->applicationKey);
        $applicationName = is_array($application) ? (string) ($application['name'] ?? $context->applicationKey) : $context->applicationKey;

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($context->pageTitle) . '</h1>';
        echo '<p>' . esc_html__('Application:', 'authcore') . ' ' . esc_html($applicationName) . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Login ID</th><th>Email</th><th>Status</th><th>Email Verified</th><th>Last Login</th></tr></thead><tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . esc_html((string) $user['user_account_id']) . '</td>';
            echo '<td>' . esc_html((string) $user['login_id']) . '</td>';
            echo '<td>' . esc_html((string) $user['email']) . '</td>';
            echo '<td>' . esc_html((string) $user['status']) . '</td>';
            echo '<td>' . esc_html(!empty($user['email_verified']) ? 'Yes' : 'No') . '</td>';
            echo '<td>' . esc_html((string) ($user['last_login_at'] ?? '')) . '</td>';
            echo '</tr>';
        }
        if ($users === []) {
            echo '<tr><td colspan="6">' . esc_html__('No users found.', 'authcore') . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
