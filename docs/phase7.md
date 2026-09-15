# AuthCore Phase 7 — Common User Admin UI

## Purpose

Phase 7 provides a shared WordPress admin user-management screen for AuthCore applications.

## Design

The UI is implemented once in AuthCore and receives an application context. Application plugins do not need to duplicate the user-management implementation.

The current v1 screen provides a read-only user list for the selected application context. It displays Login ID, email, status, email verification state, and last login time.

## Application isolation

The page requires an explicit `application_id` context. All user retrieval is performed through the AuthCore public API and therefore remains scoped to that application.

Extensions should pass their parent application's context rather than using an independent user namespace.

## Security

The page requires the WordPress `manage_options` capability. Values rendered into HTML are escaped. AuthCore user data is not queried directly from plugin code.

## Runtime verification

The implementation has been committed to the feature branch. A real WordPress admin runtime test has not yet been performed.

CRUD controls, application selector UX, and integration-specific menu placement can be refined during Phase 8 integration and Phase 10 acceptance testing.
