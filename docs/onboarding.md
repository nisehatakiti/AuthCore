# AuthCore Onboarding

## Purpose

When a registered application has no AuthCore user accounts, the application can expose an initial administrator setup screen from its own WordPress admin menu.

The onboarding flow is application-scoped and does not use WordPress `wp_users` as the application account store.

## Integration API

```php
AuthCore::registerOnboardingMenu(
    'alumni',
    'alumni-core',
    '初期設定',
    'AlumniCore 管理者の初期設定'
);
```

The application key is resolved server-side. Extensions resolve to their parent application's user namespace in the same way as common user management.

## Initial account behavior

- The screen is registered only while the application has zero AuthCore accounts.
- The current WordPress administrator must have the configured WordPress capability (default: `manage_options`).
- The form creates one active AuthCore account.
- The supplied email is marked verified because the account is created by an existing WordPress administrator.
- The first account is granted the common AuthCore `admin` capability.
- The submission is re-checked against the current account count to avoid creating an initial account after another administrator has already completed setup.
- Passwords are handled by the existing AuthCore password hashing/validation path and are never persisted in plaintext.

## AlumniCore

AlumniCore optionally integrates with AuthCore using application key `alumni`. When AuthCore is active, AlumniCore registers its user management and onboarding screens under the existing `alumni-core` admin menu.

AlumniCore has no legacy user/login system, so no user migration is required.

## Installation order

Both supported installation orders use the same integration path:

1. AlumniCore first, then AuthCore.
2. AuthCore first, then AlumniCore.

The integration is performed after `plugins_loaded` so AuthCore database migration has completed before AlumniCore attempts application registration.

## Runtime acceptance still required

Code-level implementation is complete for this onboarding slice, but WordPress runtime/E2E acceptance remains pending. The acceptance environment should test:

1. AlumniCore only: no AuthCore dependency or fatal error.
2. AuthCore + AlumniCore: `alumni` application registration.
3. Zero users: onboarding screen appears under AlumniCore.
4. Successful first-admin creation.
5. `admin` capability is granted.
6. Onboarding disappears after the first account exists.
7. User management shows the created account.
8. Repeated/concurrent first-user attempts do not silently bypass the zero-user check.
9. Both plugin installation orders work.
