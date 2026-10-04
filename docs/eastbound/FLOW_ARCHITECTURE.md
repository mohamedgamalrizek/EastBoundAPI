# FLOW Architecture Discovery

## Scope

This document records the observed architecture of the existing FLOW codebase for future EastBound DMC Management System work. It is discovery only. No EastBound features were implemented.

## Framework and Runtime

- Backend framework: Laravel `^13.0` from `composer.json`.
- PHP requirement: `^8.2`.
- First-party modular package system: `nwidart/laravel-modules ^13.0`.
- Multi-tenancy package: `stancl/tenancy ^3.10`.
- Auth/API packages: Laravel Fortify `^1.37`, Sanctum `^4.0`.
- PDF package: `barryvdh/laravel-dompdf ^3.1`.
- Image package: `intervention/image ^3.0`.
- Activity log: `spatie/laravel-activitylog ^4.10`.

## Frontend Stack

- Blade-rendered Laravel application.
- Admin UI views live under `resources/views/backend`.
- Public website views live under `resources/views/frontend`.
- Portal views live under `resources/views/backend/portal`.
- SaaS module views live under `Modules/Saas/resources/views`.
- Styling is Sass compiled by npm scripts into existing public CSS files:
  - `resources/scss/backend/*.scss`
  - `public/backend/css/sass/main.scss`
  - `public/frontend/scss/main.scss`
  - `public/frontend/scss/saas-landing-pages/saas-landing-main.scss`
- There is no Vite app for the main FLOW surface. The root `package.json` only builds Sass. `Modules/Saas/vite.config.js` exists for the SaaS module but the main app is not a SPA.

## Application Bootstrapping

- Modern Laravel bootstrap is in `bootstrap/app.php`.
- Web route files loaded:
  - `routes/web.php`
  - `routes/auth.php`
  - `routes/setting.php`
  - `routes/user.php`
- API route file: `routes/api.php`.
- The web middleware group includes installer guards, session, locale, CSRF, and route bindings.
- The API middleware group includes `EnsureAppKey`, API throttle, and route bindings. Protected API routes use `auth:sanctum`.
- Route count at discovery: `php artisan route:list` showed 813 routes.

## First-Party Modules

- `Modules/Installer`: first-party installer module with install wizard, database inspection, environment writing, migration/seed execution, and installation health checks.
- `Modules/Saas`: first-party SaaS/platform module with tenants, plans, subscriptions, domains, SaaS payments, tenant provisioning, and conditional routes enabled only when `config('saas.enabled')` is true.
- These modules are internal FLOW code. They are not third-party vendor code.

## Core Organization

- Models: `app/Models`, plus backend language/setting models under `app/Models/Backend`.
- Controllers:
  - Admin/backend: `app/Http/Controllers/Backend`
  - Public website: `app/Http/Controllers/FrontendController.php`, `SearchController.php`, `WishlistController.php`, `PaymentCallbackController.php`
  - Mobile/API: `app/Http/Controllers/Api`
  - Auth: `app/Http/Controllers/Auth`
- Requests: `app/Http/Requests`, usually grouped per module.
- Repositories: `app/Repositories/<Module>/<Module>Repository.php` and `<Module>Interface.php`.
- Repository bindings: `app/Providers/RepositoryServiceProvider.php`.
- Business services: `app/Services`, notably accounting, booking pricing/cancellation, payments, messaging, documents, loyalty, mail, and auth/social token verification.
- Observers: `app/Observers` post accounting, wallet, settlement, invoice, receipt, booking, and service-booking side effects.

## Backend/Admin Architecture

Admin is route/controller/repository/Blade based:

- Routes are mostly in `routes/web.php`, `routes/user.php`, and `routes/setting.php`.
- Admin pages are normal Blade templates under `resources/views/backend`.
- CRUD actions usually call repository interfaces injected into controllers.
- Permissions are enforced route-by-route with `hasPermission:<permission_key>`.
- List pages often calculate analytics in controllers and render existing dashboard/list components.

Important reusable UI/component patterns:

- Layout shell: `resources/views/backend/partials/master.blade.php`, header/navbar/sidebar/footer partials.
- Sidebars:
  - `resources/views/backend/partials/sidebar.blade.php`
  - `sidebar-agent.blade.php`
  - `sidebar-customer.blade.php`
  - `sidebar-staff.blade.php`
  - `sidebar-saas.blade.php`
- Shared partials/components:
  - `resources/views/components/data-table.blade.php`
  - `resources/views/components/file-uploader.blade.php`
  - `resources/views/components/list-analytics.blade.php`
  - `resources/views/components/nodata-found.blade.php`
  - `resources/views/components/page.blade.php`
  - `resources/views/components/paginate-show.blade.php`
  - `resources/views/components/phone-input.blade.php`
  - `resources/views/backend/partials/dynamic_modal.blade.php`
  - `resources/views/backend/partials/delete-ajax.blade.php`
  - `resources/views/backend/components/image-field.blade.php`
- Common UI patterns observed: Bootstrap-like tables, badges via model `statusBadge()` methods, filter bars, KPI cards, forms, modals, dropdown actions, date inputs, file/image upload fields, dashboards, charts.

## Public Website Architecture

- Public website is CMS/database driven through `FrontendController`.
- Current root `/` redirects to `/login`; the agency home page code exists in `FrontendController::home()` but is disabled by route comment.
- Public routes include packages, package booking, visa, hajj, umrah, flight landing, hotel landing, transport landing, CMS pages, blog, career, support, contact, newsletter, become-agent, booking forms, and tracking.
- Public package booking creates real `bookings` rows in pending state.
- Generic public `book/{type}` requests create service-specific records for some flows or CRM leads for others through actions.
- `become-an-agent` creates a CRM lead.

## Mobile/API Architecture

- API versioning: all FLOW app endpoints are under `/api/v1`.
- API response convention: documented in `routes/api.php` as `{success, message, data}` via `ApiReturnFormatTrait`.
- Public API: auth, home, tours, hotels, categories, hajj packages, transport types/services, flight routes, live flights, package reviews, visa services/tracking, content, enquiries, contact, settings, languages.
- Protected API: Sanctum token routes for dashboard, bookings, payments, hotels, flights, transport, passports, documents, invoices, receipts/PDFs, hajj, wallet, visa, profile, preferences, travelers, notifications, push tokens, support, reviews, guide assignments, and agent B2B endpoints.
- Additional API gate: `EnsureAppKey` checks `X-App-Key` when configured, with same-origin frontend exemptions.

## Auth and RBAC Architecture

- Staff/admin/agent/customer web logins use `App\Models\User` plus a `role_id` and a JSON-cast `permissions` attribute.
- Customers also have `App\Models\Customer` as an `Authenticatable` model with Sanctum tokens for mobile API login.
- `User::home()` routes users based on permissions:
  - SaaS: `saas_read`
  - Admin/staff: `dashboard_read`
  - Agent portal: `agent_portal_read`
  - Customer portal: `customer_portal_read`
  - Staff portal: `staff_portal_read`
- Permission enforcement is simple: `EnsurePermission` checks `in_array($permission, Auth::user()->permissions)`.
- Roles store permissions in a long text field, and users also carry denormalized permissions.

## Business Logic Location

FLOW is not purely CRUD. Important domain behavior is in services and observers:

- Booking pricing/coupons/points: `App\Services\Booking\BookingPricing`.
- Cancellation/refund rules: `App\Services\Booking\CancellationPolicy`.
- Billing and invoice/receipt/refund sync: `App\Services\Accounting\BillingService`.
- General ledger posting: `LedgerService` and account transaction observers.
- Agent commissions/wallet/withdrawals: `AgentSettlementService` and observers.
- Supplier payable balance: `SupplierAccountingService` and `SupplierTransactionObserver`.
- Payments: `App\Services\Payments\PaymentManager`, gateway classes, `PaymentIntentService`, public callback controller.
- Document PDFs: `App\Services\Documents\DocumentService`.
- Messaging: `SmsService`, `MimSmsService`, `PushService`, `WhatsAppService`.
- Mail settings/Gmail API transport: `App\Services\Mail`.

## Settings, Localization, Currency

- Settings table: `settings` with `key`, `value`; `settings()` helper is used widely.
- Settings UI includes general, appearance, AI, flight API, WhatsApp, loyalty, mail, recaptcha, payment gateways, SMS, push, API security, and social login.
- Localization:
  - `lang/en`, `lang/he`
  - `languages`, `flag_icons`, `route_lists`
  - `SetLocale` middleware
  - App language routes/controllers
  - Translation helper `___()` is used in repositories/controllers.
- Currency:
  - `currencies` table.
  - `currency_symbol()` helper is used in reports/analytics.
  - Booking amounts are stored as decimal fields without a currency column on most transactional tables.

## File/Media Architecture

- `uploads` table stores generated image/file variants.
- Public assets are stored under `public/uploads`, `public/frontend`, `public/backend`.
- Customer and visa documents exist in domain tables:
  - `customer_documents`
  - `visa_documents`
- `MovePrivateDocuments` command exists to move sensitive documents away from public paths.
- Upload handling is repository/helper based, e.g. `UploadRepository`, `StoresPublicImage`.

## Notifications, Jobs, Scheduler

- Custom `notifications` table/model, not only Laravel's default notification table.
- Notifications support `user_id`, category/read status and later `notifiable_type/notifiable_id`.
- Push device tokens are stored in `device_tokens`.
- Scheduler in `app/Console/Kernel.php`:
  - `saas:run-subscription-lifecycle` daily at 02:00
  - `passports:refresh-statuses` daily at 02:10
- No substantial queue/job architecture was found in the inspected first-party app code.

## Payments and PDFs

- Customer/service payment intents are in `payment_intents`.
- FLOW gateway implementations: bKash and SSLCommerz under `app/Services/Payments/Gateways`.
- SaaS gateway implementations are separate under `Modules/Saas/app/Payments/Gateways` and include Aamarpay, bKash, Cashfree, Flutterwave, Instamojo, Nagad, Paypal, Paystack, PayU, Razorpay, SSLCommerz, Stripe.
- Public payment callback route: `payment/callback/{gateway}/{reference}`.
- PDFs are generated with Dompdf through `DocumentService` for invoice, receipt, hotel voucher, and e-ticket.

## Upgrade-Safe EastBound Principle

Future EastBound work should be additive and should follow existing FLOW patterns:

- New migrations, models, controllers, repositories, request classes, services, observers, routes, permissions, views.
- Existing entity extension through additive nullable columns or related tables.
- Small integration points with existing booking/billing/lead/customer/supplier services.
- Avoid rewriting original migrations, renaming existing concepts, replacing Blade/admin architecture, or bypassing existing permission/settings/localization conventions.
