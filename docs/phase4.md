# AuthCore Phase 4 — Authentication

## Goal

Authenticate an AuthCore account within its application namespace using either Login ID or Email plus password.

## Authentication flow

1. Resolve the account by `application_id` and identifier.
2. Treat an email-shaped identifier as Email; otherwise treat it as Login ID.
3. Always execute a password hash check, including when the account does not exist, using a dummy hash.
4. Accept authentication only when the account exists, its status is `active`, and the password is correct.
5. Update `last_login_at` on successful authentication.
6. Return an authentication result that never contains `password_hash`.

## Failure behavior

All of the following return the same generic failure result:

- Unknown Login ID
- Unknown Email
- Wrong password
- Suspended account
- Disabled account

The failure result does not disclose whether an account exists or which credential was incorrect.

## Public API

```php
$result = AuthCore::authenticate($applicationId, $identifier, $password);

if ($result->isSuccess()) {
    $account = $result->account;
}
```

`$result->errorCode` is `invalid_credentials` for authentication failure. No account information is returned on failure.

## Account isolation

Authentication always requires an `application_id`. Login ID and Email are resolved only inside that application's namespace. An account from another application cannot authenticate through a different application ID.

Extensions must authenticate against their parent application's application ID.

## Security notes

- Passwords are checked through WordPress password hashing APIs.
- Plaintext passwords are never persisted or returned.
- Password hashes are removed from successful result payloads.
- Authentication failures intentionally use one externally visible error code.
- A dummy hash check is performed for unknown accounts to reduce username-enumeration timing differences.

## Completion criteria

- Login ID authentication works.
- Email authentication works.
- Wrong passwords fail.
- Non-active accounts fail.
- Unknown accounts fail without account-existence disclosure.
- Successful results contain account data without `password_hash`.
- `last_login_at` is updated after success.
- Public AuthCore authentication API is available.

Runtime verification requires a WordPress environment and remains pending.
