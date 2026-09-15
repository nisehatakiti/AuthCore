# AuthCore Phase 3 — User Account CRUD

## Goal

Provide the common AuthCore user-account CRUD layer while keeping every account isolated by application ID.

## Operations

- Create account
- Get account by user account ID within an application
- List accounts within an application
- Update login ID, email, password, status, and email verification state
- Delete account

## Isolation

Every repository read/write/delete operation requires `application_id` together with `user_account_id` where applicable. The same account ID from another application is therefore not addressable through this API.

Extensions do not receive a separate account namespace; they must use the parent application's application ID.

## Validation

- Login ID: required, max 190 bytes, limited to alphanumeric characters plus `._@+-`
- Email: validated with WordPress `is_email()`
- Password: minimum 8 bytes; stored using WordPress password hashing
- Status: `active`, `suspended`, or `disabled`
- Login ID and email uniqueness are enforced within each application

## Public API

```php
AuthCore::createUserAccount($applicationId, $loginId, $email, $password);
AuthCore::getUserAccount($applicationId, $userAccountId);
AuthCore::listUserAccounts($applicationId);
AuthCore::updateUserAccount($applicationId, $userAccountId, $data);
AuthCore::deleteUserAccount($applicationId, $userAccountId);
```

## Completion criteria

- CRUD operations are exposed through AuthCore.
- Account data is isolated by application ID.
- Duplicate login IDs and emails are rejected within an application.
- Passwords are never stored as plaintext.
- Account status and email verification state can be updated.
- No WordPress `wp_users` dependency is introduced.

Runtime verification requires a WordPress environment and remains pending, as with earlier phases.
