<?php

declare(strict_types=1);

namespace AuthCore\Admin;

use AuthCore\AuthCore;
use AuthCore\AuthCoreException;

final class OnboardingPage
{
    /** @var array<string, OnboardingContext> */
    private static array $contexts = [];

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_post_authcore_onboarding', [self::class, 'handle']);
    }

    public static function registerContext(
        string $applicationKey,
        string $parentMenuSlug,
        string $menuTitle = 'Setup',
        string $pageTitle = 'Initial Administrator Setup',
        string $capability = 'manage_options',
    ): string {
        $context = OnboardingContext::create($applicationKey, $parentMenuSlug, $menuTitle, $pageTitle, $capability);
        self::$contexts[$context->menuSlug] = $context;
        return $context->menuSlug;
    }

    public static function menu(): void
    {
        foreach (self::$contexts as $context) {
            if (!self::needsOnboarding($context)) {
                continue;
            }

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

    public static function handle(): void
    {
        $slug = isset($_POST['authcore_onboarding_slug']) ? sanitize_key(wp_unslash($_POST['authcore_onboarding_slug'])) : '';
        $context = self::$contexts[$slug] ?? null;

        if (!$context instanceof OnboardingContext) {
            wp_die(esc_html__('Invalid onboarding context.', 'authcore'));
        }
        if (!current_user_can($context->capability)) {
            wp_die(esc_html__('You do not have permission to access this page.', 'authcore'));
        }
        check_admin_referer('authcore_onboarding_' . $context->menuSlug);

        if (self::hasUsers($context)) {
            self::redirect($context, 'already_initialized');
        }

        $loginId = isset($_POST['login_id']) ? trim((string) wp_unslash($_POST['login_id'])) : '';
        $email = isset($_POST['email']) ? trim((string) wp_unslash($_POST['email'])) : '';
        $password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
        $passwordConfirmation = isset($_POST['password_confirmation']) ? (string) wp_unslash($_POST['password_confirmation']) : '';

        if ($password !== $passwordConfirmation) {
            self::redirect($context, 'password_mismatch');
        }

        try {
            $userId = AuthCore::createUserAccount($context->applicationId, $loginId, $email, $password, 'active');
            AuthCore::updateUserAccount($context->applicationId, $userId, ['email_verified' => true]);
            AuthCore::registerCapability($context->applicationId, 'admin');
            AuthCore::grantCapability($context->applicationId, $userId, 'admin');
        } catch (AuthCoreException $e) {
            self::redirect($context, 'error');
        }

        self::redirect($context, 'success');
    }

    private static function renderContext(OnboardingContext $context): void
    {
        if (!current_user_can($context->capability)) {
            wp_die(esc_html__('You do not have permission to access this page.', 'authcore'));
        }

        if (!self::needsOnboarding($context)) {
            echo '<div class="wrap"><h1>' . esc_html($context->pageTitle) . '</h1><div class="notice notice-success"><p>' . esc_html__('Initial administrator setup has already been completed.', 'authcore') . '</p></div></div>';
            return;
        }

        $application = AuthCore::getApplication($context->applicationKey);
        $applicationName = is_array($application) ? (string) ($application['name'] ?? $context->applicationKey) : $context->applicationKey;
        $message = isset($_GET['authcore_onboarding_message']) ? sanitize_key(wp_unslash($_GET['authcore_onboarding_message'])) : '';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($context->pageTitle) . '</h1>';
        echo '<p>' . esc_html__('Application:', 'authcore') . ' ' . esc_html($applicationName) . '</p>';
        echo '<p>' . esc_html__('Create the first AuthCore account for this application. This account will receive the common admin capability.', 'authcore') . '</p>';

        if ($message === 'password_mismatch') {
            echo '<div class="notice notice-error"><p>' . esc_html__('The passwords do not match.', 'authcore') . '</p></div>';
        } elseif ($message === 'error') {
            echo '<div class="notice notice-error"><p>' . esc_html__('The administrator account could not be created. Please check the values and try again.', 'authcore') . '</p></div>';
        } elseif ($message === 'already_initialized') {
            echo '<div class="notice notice-info"><p>' . esc_html__('Initial administrator setup has already been completed.', 'authcore') . '</p></div>';
        }

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('authcore_onboarding_' . $context->menuSlug);
        echo '<input type="hidden" name="action" value="authcore_onboarding">';
        echo '<input type="hidden" name="authcore_onboarding_slug" value="' . esc_attr($context->menuSlug) . '">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="authcore-login-id">' . esc_html__('Login ID', 'authcore') . '</label></th><td><input name="login_id" id="authcore-login-id" type="text" class="regular-text" required maxlength="190" autocomplete="username"></td></tr>';
        echo '<tr><th scope="row"><label for="authcore-email">' . esc_html__('Email', 'authcore') . '</label></th><td><input name="email" id="authcore-email" type="email" class="regular-text" required maxlength="190" autocomplete="email"></td></tr>';
        echo '<tr><th scope="row"><label for="authcore-password">' . esc_html__('Password', 'authcore') . '</label></th><td><input name="password" id="authcore-password" type="password" class="regular-text" required minlength="8" autocomplete="new-password"><p class="description">' . esc_html__('At least 8 characters.', 'authcore') . '</p></td></tr>';
        echo '<tr><th scope="row"><label for="authcore-password-confirmation">' . esc_html__('Confirm Password', 'authcore') . '</label></th><td><input name="password_confirmation" id="authcore-password-confirmation" type="password" class="regular-text" required minlength="8" autocomplete="new-password"></td></tr>';
        echo '</tbody></table>';
        submit_button(__('Create Administrator', 'authcore'));
        echo '</form></div>';
    }

    private static function needsOnboarding(OnboardingContext $context): bool
    {
        return !self::hasUsers($context);
    }

    private static function hasUsers(OnboardingContext $context): bool
    {
        return AuthCore::listUserAccounts($context->applicationId, 1, 0) !== [];
    }

    private static function redirect(OnboardingContext $context, string $message): void
    {
        $url = add_query_arg(
            [
                'page' => $context->menuSlug,
                'authcore_onboarding_message' => $message,
            ],
            admin_url('admin.php')
        );
        wp_safe_redirect($url);
        exit;
    }
}
