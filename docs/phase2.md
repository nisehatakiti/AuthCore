# AuthCore Phase 2 — Application / Extension Registration

## Goal

Register nisehatakiti application and extension plugins from their AuthCore metadata and persist the registration in the AuthCore application registry.

## Registration model

- `application` creates an independent AuthCore application namespace.
- `extension` is registered as a child of an existing `application` and shares that application's account namespace.
- `AuthCore Parent Application` is resolved by `application_key`.
- An extension cannot be registered when its parent is missing or is itself an extension.
- Registration is idempotent by `application_key`.
- A previously registered key cannot change between application and extension types.

## Automatic discovery

At `plugins_loaded`, AuthCore discovers active plugins with AuthCore metadata. Applications are registered before extensions so parent applications are available when extensions are processed.

Registration errors do not stop WordPress boot. AuthCore fires `authcore/registration_error` with the exception and plugin file so the host application can log or surface the problem.

## Public API

```php
AuthCore::registerPlugin(__FILE__);
AuthCore::getApplication('alumni');
```

Automatic discovery means normal nisehatakiti applications and extensions do not need to implement separate registration logic.

## Database change

The applications table now includes:

- `type`
- `parent_application_id`

Schema version: `1.1.0`.

Existing rows default to `application`, preserving Phase 1 registrations.

## Completion criteria

- Application metadata can be registered.
- Extension metadata can be registered against a valid parent application.
- Parent lookup uses application key.
- Duplicate registration is idempotent.
- Application/extension type conflicts are rejected.
- Missing extension parent is rejected.
- Active plugins are discovered automatically.
- Phase 1 database migrates from `1.0.0` to `1.1.0` through `dbDelta()`.
