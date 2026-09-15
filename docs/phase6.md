# AuthCore Phase 6 — Authorization

## Purpose

Phase 6 provides application-scoped capability authorization for AuthCore.

## Model

- Capabilities belong to an Application.
- User capability grants belong to the same Application context.
- An Extension uses its parent Application's authorization namespace.
- User accounts cannot be granted or checked against another application's capability space.
- v1 intentionally does not introduce roles, groups, advanced RBAC, or cross-application permissions.

## API

```php
AuthCore::registerCapability($applicationId, 'alumni.manage_users');
AuthCore::grantCapability($applicationId, $userAccountId, 'alumni.manage_users');
AuthCore::revokeCapability($applicationId, $userAccountId, 'alumni.manage_users');
AuthCore::hasCapability($applicationId, $userAccountId, 'alumni.manage_users');
AuthCore::hasCurrentUserCapability($applicationId, 'alumni.manage_users');
```

Capability identifiers use lower-case letters, numbers, dots, underscores, colons, and hyphens, and are limited to 100 bytes.

## Security behavior

- Application context is resolved before authorization operations.
- Inactive applications and inactive extension parents are rejected.
- User accounts are checked inside the resolved application context.
- Unknown users return `false` for capability checks.
- Capability grants are idempotent.
- Password hashes and session tokens are not exposed by authorization APIs.

## Database

Phase 6 adds:

- `authcore_capabilities`
- `authcore_user_capabilities`

Database version is `1.3.0`.

## Runtime verification

The implementation has been committed to the GitHub feature branch. A real WordPress runtime test has not yet been performed; Phase 10 remains the acceptance-test stage.
