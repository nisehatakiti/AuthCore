# AuthCore Phase 9 — Security / Edge Cases

## Purpose

Phase 9 hardens the authentication foundation against invalid application contexts, oversized authentication input, session persistence failures, and capability persistence failures.

## Application boundary

All User Account CRUD operations now resolve the AuthCore Application Context before accessing account data.

- inactive or unknown applications are rejected
- extensions resolve to their active parent Application
- direct user-account operations cannot silently operate in an inactive or independent extension namespace
- account IDs remain scoped to the resolved Application

Session and authorization operations already enforce the same context rule.

## Authentication input

Authentication rejects empty or oversized identifiers and passwords before database work. Password input is limited to 4096 bytes to prevent unnecessarily expensive password-hashing/checking work from unbounded input.

Authentication continues to use a dummy password hash for missing accounts and returns the same generic `invalid_credentials` result for missing, inactive, or incorrect credentials.

Successful authentication does not return the password hash.

## Session security

Session tokens remain cryptographically random 32-byte values represented as 64-character hexadecimal strings. Only SHA-256 hashes are stored in the database.

Session cookies remain:

- HttpOnly
- Secure when HTTPS is active
- SameSite=Lax
- Application-context scoped
- 14-day lifetime

A successful login destroys the existing context session before issuing a new token, preventing session fixation through reuse of the previous token.

Database write failures for session creation, update, deletion, and expiration cleanup now raise `AuthCoreException` instead of being silently ignored.

## Capability security

Capability definitions and grants remain Application-scoped. Database failures during capability creation, grant, and revoke now raise `AuthCoreException`.

Capability identifiers continue to be validated before authorization decisions. Unknown users cannot be checked or granted across application boundaries.

## Admin boundary

The common user-management UI uses a registered Integration Context. The browser does not supply an arbitrary AuthCore application ID. WordPress admin capability checks are performed before rendering.

## Data exposure

AuthCore APIs do not expose password hashes. Session cookies contain only opaque random tokens. Application and Extension namespaces are resolved internally.

## Runtime verification

Phase 9 changes have been committed to the feature branch. Static/code-level hardening is complete, but a real WordPress runtime and security test suite has not yet been executed.

Phase 10 is the final E2E and v1 acceptance-test phase.
