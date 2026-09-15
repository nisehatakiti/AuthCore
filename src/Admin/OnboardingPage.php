<?php

declare(strict_types=1);

namespace AuthCore\Admin;

use AuthCore\AuthCore;
use AuthCore\AuthCoreException;

final class OnboardingPage
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
        string $menuTitle = 'Onboarding',
        string $pageTitle = 'Application Setup',
        string $capability = 'manage_options',
        string $adminCapability = 'admin',
    ): string {
        $context = UserManagementContext::create($applicationKey, $parentMenuSlug, $menuTitle, $pageTitle, $capability);
        self::$contexts[$context->menuSlug] = $context;
        self::$contexts[$context->menuSlug]->adminCapability = $adminCapability;
        return $context->menuSlug;
    }

    public static function menu(): void
    {
        foreach (self::$contexts as $context) {
            if (!current_user_can($context->capability) || !AuthCore::isApplicationOnboardingRequired($context->applicationId)) {
                continue;
            }
            add_submenu_page(
                $context->parentMenuSlug,
                $context->pageTitle,
                $context->menuTitle,
                $context->capability,
                $context->menuSlug . '-onboarding',
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

        $application = AuthCore::getApplication($context->applicationKey);
        $applicationName = is_array($application) ? (string) ($application['name'] ?? $context->applicationKey) : $context->applicationKey;

        if (!AuthCore::isApplicationOnboardingRequired($context->applicationId)) {
            echo '<div class="wrap"><h1>' . esc_html($context->pageTitle) . '</h1><div class="notice notice-success"><p>' . esc_html__('Application onboarding is already complete.', 'authcore') . '</p></div></div>';
            return;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['authcore_onboarding_submit'])) {
            check_admin_referer('authcore_onboarding_' . $context->menuSlug);
            try {
                $userAccountId = AuthCore::createInitialAdmin(
                    $context->applicationId,
                    (string) ($_POST['login_id'] ?? ''),
                    (string) ($_POST['email'] ?? ''),
                    (string) ($_POST['password'] ?? ''),
                );
                wp_safe_redirect(add_query_arg(['page' => $context->menuSlug . '-onboarding', 'authcore_onboarding' => 'complete'], admin_url('admin.php')));
                exit;
            } catch (AuthCoreException $exception) {
                $error = $exception->getMessage();
            }
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($context->pageTitle) . '</h1>';
        echo '<p>' . esc_html(sprintf(__('No AuthCore user exists for %s. Create the first administrator account to complete setup.', 'authcore'), $applicationName)) . '</p>';
        if (isset($_GET['authcore_onboarding']) && $_GET['authcore_onboarding'] === 'complete') {
            echo '<div class="notice notice-success"><p>' . esc_html__('Administrator account created. Application onboarding is complete.', 'authcore') . '</p></div>';
        }
        if ($error !== null) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        echo '<form method="post">';
        wp_nonce_field('authcore_onboarding_' . $context->menuSlug);
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label for="authcore-login-id">' . esc_html__('Login ID', 'authcore') . '</label></th><td><input id="authcore-login-id" name="login_id" type="text" class="regular-text" required maxlength="190" autocomplete="username"></td></tr>';
        echo '<tr><th><label for="authcore-email">' . esc_html__('Email', 'authcore') . '</label></th><td><input id="authcore-email" name="email" type="email" class="regular-text" required maxlength="190" autocomplete="email"></td></tr>';
        echo '<tr><th><label for="authcore-password">' . esc_html__('Password', 'authcore') . '</label></th><td><input id="authcore-password" name="password" type="password" class="regular-text" required minlength="8" maxlength="4096" autocomplete="new-password"><p class="description">' . esc_html__('Use at least 8 characters.', 'authcore') . '</p></td></tr>';
        echo '</tbody></table>';
        echo '<p><button type="submit" name="authcore_onboarding_submit" value="1" class="button button-primary">' . esc_html__('Create Administrator Account', 'authcore') . '</button></p>';
        echo '</form></div>';
    }
}
