# FLOW Security and Test Baseline

## Security Architecture Observed

### Authentication

- Web auth uses `App\Models\User` for staff/admin/agent/customer portal logins.
- Customer API auth uses `App\Models\Customer` as an `Authenticatable` model with Sanctum.
- API protected routes use `auth:sanctum`.
- Fortify is installed and configured.
- Social auth exists for web/mobile with provider settings.
- OTP and password reset flows exist.

### Authorization

- Route-level permissions use `hasPermission:<key>`.
- `EnsurePermission` checks `Auth::user()->permissions` JSON array.
- Roles exist but effective authorization is denormalized on users.
- Admin routes are not protected by one global admin middleware; authorization is per route.

### CSRF and Web Middleware

- Web middleware includes sessions and `VerifyCsrfToken`.
- Payment callback route accepts GET/POST publicly because providers call without session/CSRF.
- API routes are stateless and use app key/throttle/Sanctum rather than CSRF.

### API Security

- Public API credential endpoints use throttle limiters.
- General API throttle is 60/minute per user/IP.
- `EnsureAppKey` can require `X-App-Key`.
- App key is not treated as real authentication in comments; it is an outer traffic filter.

### Uploads/Documents

- Upload metadata table exists.
- Public uploads live under `public/uploads`.
- Visa document privacy tests exist.
- `MovePrivateDocuments` command indicates migration away from public document URLs.
- Future EastBound financial/customer/trip documents should use private storage patterns and scoped download routes.

### Financial Data

- Billing, ledger, supplier, wallet, and agent settlement are observer/service-driven.
- Direct writes to balances are risky.
- EastBound financial extensions must use services/observers and transactional/idempotent posting.

### Activity/Audit

- Spatie activity log is installed.
- Many models use `LogsActivity`.
- EastBound should log important lifecycle events: lead intake, quotation revision, approval, acceptance, booking confirmation, vendor purchase posting, payment/refund, operations status changes.

## Security Risks to Consider Before EastBound Implementation

| Area | Risk | Recommendation |
|---|---|---|
| Permission storage | User permissions are denormalized arrays; changes to roles may not automatically update every user unless existing code handles it. | Add EastBound permissions through existing seed/update pattern and test role/user assignment. |
| Route authorization | Authorization is route-by-route, so new routes can be accidentally exposed if middleware is omitted. | Every EastBound route must include explicit `auth` and `hasPermission` gates or appropriate public API security. |
| API lead intake | Public lead endpoint can be abused/spammed. | Throttle, app key/signature, recaptcha if web form, validation, audit, and idempotency. |
| IDOR | Existing controllers vary in scoping; customer/agent APIs must scope by authenticated account. | Follow current portal/API scoping patterns and add feature tests. |
| Uploads | Public uploads can expose sensitive documents. | Use private disk for passports, vendor docs, quotation attachments, trip docs. |
| Mass assignment | Models have broad `$fillable` arrays. | Use FormRequests/services, never pass untrusted request all-fields to sensitive models. |
| Accounting side effects | Observer-driven posting can double-post if new writes are not idempotent. | Use source morph/idempotent references and transactions. |
| Secrets/settings | Payment/social/mail credentials are stored in settings. | Use existing secret/encrypted settings approach; do not add env-only hidden parallel config for EastBound. |

## Baseline Commands Run

Commands were run from:

```text
/Volumes/Home/Personal Work/EastBound/Platform/WebSourceCode
```

### Composer Validation

Command:

```bash
composer validate --no-check-publish
```

Result:

- Passed.
- Output: `./composer.json is valid`.

### PHP Syntax Check

Command:

```bash
find app routes config database/migrations Modules -type f -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n 1 php -l
```

Result:

- Passed with no syntax errors detected across checked app, routes, config, migrations, and first-party modules.
- Baseline deprecation warnings appeared for implicit nullable parameters in:
  - `app/Repositories/Role/RoleInterface.php`
  - `app/Repositories/Role/RoleRepository.php`
  - `app/Repositories/Language/LanguageRepository.php`
  - `app/Repositories/Language/LanguageInterface.php`
  - `app/Repositories/User/UserInterface.php`
  - `app/Repositories/User/UserRepository.php`
  - `app/Repositories/Upload/UploadInterface.php`
  - `app/Repositories/Upload/UploadRepository.php`

### Test Suite

Command:

```bash
php artisan test
```

Result:

- Failed.
- Summary: 13 passed, 207 failed.
- Common failure cause: database connection blocked/unavailable.
- Representative error:

```text
SQLSTATE[HY000] [2002] Operation not permitted
Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: flow
```

Baseline interpretation:

- Current environment cannot connect to MySQL at `127.0.0.1:3306`.
- Treat the failing tests as environment/database baseline failures until a test database is configured.
- Do not attribute these failures to EastBound work.

### Frontend Build

Command:

```bash
npm run build
```

Result:

- Failed.
- Cause: local `sass` binary is missing.
- Output:

```text
sh: sass: command not found
```

Baseline interpretation:

- Dependencies may not be installed or `node_modules/.bin` is unavailable.
- Do not treat this as a CSS/app regression.

## Baseline Issue Register

| ID | Area | Issue | Impact | Recommended next step |
|---|---|---|---|---|
| BL-001 | Tests | MySQL connection to `127.0.0.1:3306` blocked/unavailable for database `flow` | Feature tests cannot establish reliable baseline | Configure test DB or `.env.testing`; rerun `php artisan test`. |
| BL-002 | Frontend build | `sass` command missing | CSS build cannot run | Run package install or ensure `sass` devDependency binary exists. |
| BL-003 | PHP compatibility | Implicit nullable parameter deprecation warnings | Future PHP versions may turn noise into failures | Fix in separate maintenance pass, not during EastBound feature work. |
| BL-004 | Git metadata | Current path is not detected as a Git repository | Cannot use git status/diff as local change audit | Confirm repo root or mount settings before implementation phases. |

## EastBound Security Extension Rules

- New admin routes must use `auth` and `hasPermission`.
- New public lead/API routes must use throttling and existing API response style.
- Sensitive uploads must not be publicly guessable.
- Financial writes must go through services with source references and transactions.
- Add tests for every new permission boundary and account-scope boundary.
