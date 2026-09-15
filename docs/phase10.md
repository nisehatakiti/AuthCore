# AuthCore Phase 10 — E2E / v1 Acceptance

## Purpose

Phase 10 is the final acceptance phase for AuthCore v1. It verifies the complete authentication flow and the application/extension isolation rules before release.

## Acceptance scenarios

### 1. Application registration

- [ ] An Application registers from valid AuthCore metadata.
- [ ] Registration is idempotent by Application Key.
- [ ] Duplicate keys cannot silently change Application/Extension type.
- [ ] Invalid or inactive Application contexts are rejected.

### 2. Extension registration

- [ ] An Extension registers against an existing Application Key.
- [ ] An Extension cannot register without a valid parent Application.
- [ ] Extension user operations resolve to the parent Application context.

### 3. Account isolation

Create accounts with the same login ID and email in two Applications.

- [ ] Both accounts can coexist.
- [ ] Application A cannot retrieve Application B's account by ID.
- [ ] Application A cannot authenticate against Application B's account.
- [ ] Capabilities do not cross Application boundaries.

### 4. Authentication

- [ ] Correct login ID + password succeeds.
- [ ] Correct email + password succeeds.
- [ ] Wrong password fails with the generic authentication error.
- [ ] Unknown account fails with the same generic authentication error.
- [ ] Suspended account cannot authenticate.
- [ ] Disabled account cannot authenticate.
- [ ] Password hash is never returned by the public authentication result.

### 5. Session lifecycle

- [ ] Successful login creates an application-scoped session.
- [ ] Current account retrieval succeeds with a valid cookie.
- [ ] Logout invalidates the session and clears the cookie.
- [ ] Expired sessions are rejected.
- [ ] Suspended/disabled accounts lose access even when an old session exists.
- [ ] A new login replaces the previous context session.

### 6. Authorization

- [ ] A registered capability can be granted to an account in its Application.
- [ ] An unregistered capability cannot be granted.
- [ ] A capability grant is visible only in the owning Application context.
- [ ] Revocation removes the grant.
- [ ] Current-user capability checks follow the session's Application context.

### 7. Common admin integration

- [ ] An Application can register the common user-management submenu.
- [ ] The menu is attached to the product's existing WordPress admin menu.
- [ ] The browser cannot select an arbitrary AuthCore Application ID.
- [ ] WordPress capability checks prevent unauthorized access.
- [ ] An Extension's common user-management page operates on the parent Application's users.

### 8. Initial administrator onboarding

- [ ] An Application with zero AuthCore users exposes the onboarding submenu.
- [ ] The onboarding page is attached to the product's existing WordPress admin menu.
- [ ] The onboarding form requires login ID, email, password, and password confirmation.
- [ ] Password confirmation mismatch does not create an account.
- [ ] The first account is created in the resolved Application context only.
- [ ] The first account receives the common `admin` capability.
- [ ] The created account is marked as active and email-verified for initial setup.
- [ ] Once an account exists, the onboarding page is no longer presented as a setup action.
- [ ] A second initialization attempt is rejected/treated as already initialized.
- [ ] An Extension's onboarding resolves to the parent Application and does not create a separate user space.

### 9. AlumniCore integration acceptance

AlumniCore is the first representative Application integration. AlumniCore has no pre-existing user or login system, so no user migration is required.

- [ ] AlumniCore registers as AuthCore Application Key `alumni` when AuthCore is active.
- [ ] Installing/activating AuthCore after AlumniCore does not require reinstalling AlumniCore.
- [ ] Installing/activating AlumniCore after AuthCore registers `alumni` automatically.
- [ ] AlumniCore's existing `alumni-core` admin menu contains the AuthCore user-management entry.
- [ ] With zero AlumniCore users, the initial administrator onboarding entry is available.
- [ ] Creating the first AlumniCore administrator creates an AuthCore account scoped to `alumni`.
- [ ] The initial AlumniCore administrator receives the common `admin` capability.
- [ ] AlumniCore continues to load normally when AuthCore is not installed or active.

## Security acceptance

- [ ] Session cookies are HttpOnly.
- [ ] Session cookies are Secure when HTTPS is active.
- [ ] Session cookies use SameSite=Lax.
- [ ] Only hashed session tokens are stored in the database.
- [ ] Authentication performs a password check for unknown accounts.
- [ ] Oversized authentication input is rejected.
- [ ] Database failures are not silently treated as successful persistence operations.
- [ ] User-facing HTML output is escaped.
- [ ] Onboarding POST requests require a WordPress nonce and the configured admin capability.
- [ ] Onboarding cannot target an arbitrary Application ID supplied by the browser.

## Runtime status

The checklist is the release gate. Code inspection alone does not mark runtime scenarios as passed. A real WordPress installation with representative Application and Extension plugins must be used to mark the runtime boxes complete.

Until that environment is exercised, AuthCore should be described as **implementation-complete but runtime acceptance pending**, not as fully E2E-verified.
