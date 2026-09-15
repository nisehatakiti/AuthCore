# AuthCore Phase 11 — Application Administrator Onboarding

## Purpose

When a registered AuthCore Application has no user accounts, the Application can expose an onboarding screen from its existing WordPress admin menu. The onboarding creates the first AuthCore user account and grants that account the reserved initial administrator capability `admin`.

## Integration

Applications register the onboarding menu with:

```php
AuthCore::registerOnboardingMenu(
    'alumni',
    'alumni-core',
    'Onboarding',
    'AlumniCore Setup'
);
```

The onboarding uses the same application-context resolution as User Management. If an Extension registers onboarding, its parent Application is used as the user namespace.

## Behavior

1. The onboarding menu is displayed only when the resolved Application has zero AuthCore users.
2. Access requires the configured WordPress admin capability (default: `manage_options`).
3. The form collects Login ID, Email, and Password.
4. A nonce protects the form submission.
5. The server re-checks that the Application still has no users before creating the first account.
6. The first account is created as `active`.
7. AuthCore registers the `admin` capability and grants it to the new account.
8. After creation, onboarding is no longer offered because the Application now has a user.

## Security

- Application IDs are resolved server-side; the browser cannot select an arbitrary AuthCore application ID.
- Extension onboarding resolves to the parent Application.
- Password validation and hashing are delegated to the existing User Account layer.
- Capability registration and grant are performed through the existing Authorization layer.
- The onboarding form uses a WordPress nonce and capability check.

## Acceptance

- Application with zero users shows onboarding under the Application's existing admin menu.
- Valid first administrator details create exactly one active AuthCore account and grant `admin`.
- Invalid Login ID, Email, or Password is rejected using existing validation.
- Application with an existing user does not show onboarding.
- Extension onboarding operates in the parent Application user namespace.
- A second submission after completion is rejected by the server-side zero-user check.

Runtime verification remains pending until exercised in a real WordPress environment.
