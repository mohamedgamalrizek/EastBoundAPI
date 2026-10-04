# EastBound Change Policy

## Prime Directive

FLOW is the base system. EastBound work must extend FLOW with minimum invasive changes.

## Prohibited Without Explicit Approval

- Rebuilding the application.
- Migrating to another framework or frontend stack.
- Creating a parallel app.
- Renaming existing tables, models, routes, controllers, services, folders, or conventions.
- Removing existing functionality.
- Rewriting original migrations.
- Replacing the RBAC/auth/settings/localization/accounting architecture.
- Editing `vendor/`, `node_modules/`, or third-party package source.

## Default Extension Pattern

When implementing EastBound:

1. Reuse an existing FLOW concept if semantics match.
2. Extend through additive migrations or related tables.
3. Add a new module only when no suitable FLOW concept exists.
4. Use services/observers/events for workflow side effects.
5. Use existing admin Blade layout/components.
6. Use existing route and permission conventions.
7. Preserve existing mobile API response/auth conventions.

## Database Rules

- New migrations only.
- No edits to historical FLOW migrations.
- Nullable additive columns for small extensions.
- Related tables for complex extensions.
- Source morphs/references for accounting-related links.
- Do not store sensitive documents in public paths.

## Code Organization Rules

Follow existing patterns:

- `app/Models`
- `app/Http/Controllers/Backend`
- `app/Http/Controllers/Api`
- `app/Http/Requests/<Module>`
- `app/Repositories/<Module>`
- `app/Services`
- `app/Observers` only when every write path needs side effects
- `resources/views/backend/<module>`
- `resources/views/pdf` for generated PDFs

## Permission Rules

- Every admin route must include `auth` and `hasPermission`.
- Every portal route must scope records to the logged-in account.
- Every API route must follow `/api/v1` conventions unless explicitly approved.
- Public endpoints must be throttled and validated.

## Accounting Rules

- Do not directly mutate account, customer wallet, supplier, agent wallet, invoice paid, or refunded balances outside existing services.
- New financial postings must be idempotent.
- Use source references/morphs so posts can be rebuilt or removed.
- Add tests before connecting new EastBound objects to ledger/supplier transactions.

## UI Rules

- Reuse backend master layout/sidebar/components.
- Reuse existing table/form/modal/badge/filter/card/chart patterns.
- Avoid redesigning working FLOW modules.
- Add EastBound screens as new modules or pages, not replacement screens.

## API Rules

- Preserve existing `/api/v1` routes.
- Preserve response envelope.
- Preserve Sanctum auth.
- Preserve `EnsureAppKey` behavior.
- Add new endpoints with validation, throttling, and tests.

## Backward Compatibility Checklist

Before merging any EastBound change, verify:

- Existing package bookings still work.
- Existing customers and portal access still work.
- Existing admin pages still load.
- Existing mobile API auth and booking routes still work.
- Existing invoices/receipts/refunds still post correctly.
- Existing suppliers and ledger still work.
- Existing public site routes still work.
- Existing SaaS/installer routes are not affected.

## Classification Policy

For every EastBound requirement, classify:

- A: already exists.
- B: extend existing.
- C: add new module.
- D: requires core change.

Use D only when:

- Extension cannot preserve semantics.
- A related table/wrapper/service cannot solve the requirement.
- A core behavior must change for correctness.

Every D item must document:

- Why extension is insufficient.
- Files affected.
- Risk.
- Upgrade impact.
- Safer alternatives considered.

## Current D-Class Items

None approved or required at discovery time.

Potential future D-class candidates must be reviewed before implementation:

- Recasting `bookings` as generic trips.
- Retrofitting every legacy financial table for multi-currency.
- Replacing RBAC.
