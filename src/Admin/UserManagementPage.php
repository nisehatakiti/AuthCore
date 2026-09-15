<?php

declare(strict_types=1);

namespace AuthCore\Admin;

use AuthCore\AuthCore;

final class UserManagementPage
{
    public static function register(): void { add_action('admin_menu', [self::class, 'menu']); }

    public static function menu(): void
    {
        add_menu_page('AuthCore Users', 'AuthCore Users', 'manage_options', 'authcore-users', [self::class, 'render'], 'dashicons-admin-users');
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You do not have permission to access this page.', 'authcore'));
        $applicationId = isset($_GET['application_id']) ? absint($_GET['application_id']) : 0;
        echo '<div class="wrap"><h1>' . esc_html__('AuthCore Users', 'authcore') . '</h1>';
        if ($applicationId <= 0) { echo '<p>' . esc_html__('Select an application context to manage users.', 'authcore') . '</p></div>'; return; }
        $users = AuthCore::listUserAccounts($applicationId, 100, 0);
        echo '<p>' . esc_html__('Application ID:', 'authcore') . ' ' . esc_html((string) $applicationId) . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Login ID</th><th>Email</th><th>Status</th><th>Email Verified</th><th>Last Login</th></tr></thead><tbody>';
        foreach ($users as $user) {
            echo '<tr><td>' . esc_html((string) $user['user_account_id']) . '</td><td>' . esc_html((string) $user['login_id']) . '</td><td>' . esc_html((string) $user['email']) . '</td><td>' . esc_html((string) $user['status']) . '</td><td>' . esc_html(!empty($user['email_verified']) ? 'Yes' : 'No') . '</td><td>' . esc_html((string) ($user['last_login_at'] ?? '')) . '</td></tr>';
        }
        if ($users === []) echo '<tr><td colspan="6">' . esc_html__('No users found.', 'authcore') . '</td></tr>';
        echo '</tbody></table></div>';
    }
}
