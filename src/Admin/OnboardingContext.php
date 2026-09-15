<?php

declare(strict_types=1);

namespace AuthCore\Admin;

use AuthCore\ApplicationRegistry;
use AuthCore\AuthCoreException;

final class OnboardingContext
{
    public function __construct(
        public readonly string $applicationKey,
        public readonly int $applicationId,
        public readonly string $menuSlug,
        public readonly string $parentMenuSlug,
        public readonly string $menuTitle,
        public readonly string $pageTitle,
        public readonly string $capability,
    ) {}

    public static function create(
        string $applicationKey,
        string $parentMenuSlug,
        string $menuTitle = 'Setup',
        string $pageTitle = 'Initial Administrator Setup',
        string $capability = 'manage_options',
    ): self {
        $applicationKey = strtolower(trim($applicationKey));
        $parentMenuSlug = trim($parentMenuSlug);
        $menuTitle = trim($menuTitle);
        $pageTitle = trim($pageTitle);
        $capability = trim($capability);

        if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $applicationKey)) {
            throw new AuthCoreException('Invalid AuthCore application key.');
        }
        if ($parentMenuSlug === '' || $menuTitle === '' || $pageTitle === '' || $capability === '') {
            throw new AuthCoreException('Invalid AuthCore onboarding menu configuration.');
        }

        $application = (new ApplicationRegistry())->findByKey($applicationKey);
        if ($application === null) {
            throw new AuthCoreException(sprintf('AuthCore application "%s" is not registered.', $applicationKey));
        }

        if (($application['type'] ?? '') === 'extension') {
            $parentId = isset($application['parent_application_id']) ? (int) $application['parent_application_id'] : 0;
            if ($parentId <= 0) {
                throw new AuthCoreException(sprintf('AuthCore extension "%s" has no parent application.', $applicationKey));
            }
            $parent = (new ApplicationRegistry())->findById($parentId);
            if ($parent === null || ($parent['type'] ?? '') !== 'application') {
                throw new AuthCoreException(sprintf('AuthCore extension "%s" has an invalid parent application.', $applicationKey));
            }
            $applicationId = $parentId;
            $contextKey = (string) $parent['application_key'];
        } else {
            $applicationId = (int) $application['application_id'];
            $contextKey = $applicationKey;
        }

        return new self(
            $contextKey,
            $applicationId,
            'authcore-onboarding-' . substr(hash('sha256', $contextKey), 0, 16),
            $parentMenuSlug,
            $menuTitle,
            $pageTitle,
            $capability,
        );
    }
}
