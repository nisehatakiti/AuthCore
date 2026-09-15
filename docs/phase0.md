# Phase 0 — Foundation

## Scope

Phase 0 establishes the minimum executable foundation for AuthCore v1.

### Included

- WordPress plugin bootstrap
- AuthCore namespace and lightweight autoloader
- Central plugin boot lifecycle
- Activation / deactivation hooks
- Version and DB-version constants
- Basic public AuthCore facade
- AuthCore plugin metadata parser for Application / Extension declarations
- Common AuthCore exception type

### Not included

- Database tables
- Application registration persistence
- User accounts
- Login / logout
- Sessions
- Capability / authorization implementation
- User management UI

Those are implemented in later phases according to the AuthCore v1 roadmap.

## Completion criteria

Phase 0 is complete when:

1. AuthCore can be activated without a PHP fatal error.
2. AuthCore can be deactivated without destructive cleanup.
3. The plugin boot lifecycle runs once.
4. `AuthCore::version()` returns the plugin version.
5. `AuthCore::isLoaded()` becomes true after initialization.
6. AuthCore plugin metadata can be parsed and validated for both `application` and `extension` declarations.
7. Invalid or incomplete metadata is rejected without creating persistent application data.
8. The project structure is ready for the Phase 1 database implementation.

## Metadata validation rules in Phase 0

The parser validates the rules already defined by the v1.0 metadata specification:

- type must be `application` or `extension`
- Application Key must use lowercase letters, numbers, and hyphens
- Application Name is required
- Extension Parent Application is required and must use the same key format
- Application must not declare a Parent Application

Persistence and parent-application resolution are intentionally deferred to Phase 2.
