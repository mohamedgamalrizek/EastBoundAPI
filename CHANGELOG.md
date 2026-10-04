# Changelog

All notable changes to the FLOW web backend (Laravel) are listed here.

## 1.2.0 — 2026-09-23

Three half-built features finished. All of them hang off a booking, so they
went in together.

**Promo codes now work**
- The Coupons module was CRUD nothing ever read: no booking path looked a code
  up, so `min_spend`, `usage_limit` and `expires_at` were decoration. A code
  can now be entered when booking from the mobile app, the customer portal,
  the public website and the back-office form, and all five paths price
  through one service (`App\Services\Booking\BookingPricing`).
- `coupons.used_count` is counted for the first time, claimed with an atomic
  conditional UPDATE so two people redeeming the last use cannot both win.
  Cancelling a booking hands the use back; reinstating it takes it again.
- A code that cannot be used never fails the booking — it is priced without
  the discount and the reason comes back to the customer.

**Loyalty points can be spent**
- Points could be earned but not redeemed; a balance only ever went up.
  They now come off a tour booking as a discount, bounded by three new
  settings (*Settings → Loyalty*): the value of a point, the fewest that may
  be spent at once, and the largest share of a booking they may cover.
- Redemption is a discount and not wallet credit on purpose: the wallet is a
  liability account the ledger reconciles against money actually taken, which
  is why `POST /wallet/topup` was removed in 1.1.0. A discount lowers the
  sale, so the invoice, the receipt and the books stay in agreement.
- Points are now earned on what the customer actually paid, and a booking that
  stops being paid has its reward reversed — previously a cancelled, refunded
  trip still paid out rewards.

**Verified booking reviews**
- New `reviews` table and a Reviews module under Marketing. A customer may
  only review a tour they paid for and have travelled on, so the "Verified
  booking" badge on the public page is a property of the record rather than a
  claim. One review per trip, enforced by a unique index as well as by the
  service.
- Moderated by default (*Settings → Loyalty → Reviews* switches that off).
  Staff approve, reject and reply; they cannot edit what the customer wrote.
- Ratings come from approved reviews only, and appear on the public package
  page, the portal's Tours list, the app's tour cards and tour detail.
- New endpoints: `GET /api/v1/packages/{id}/reviews` (public),
  `GET /api/v1/reviews`, `POST /api/v1/bookings/{id}/review`, and
  `POST /api/v1/bookings/quote` for pricing a trip before committing to it.

**Upgrading**
- `php artisan migrate` adds the tables and backfills `bookings.gross_amount`
  from the amount already recorded, and grants the new `review_*` permissions
  to existing staff whose role now carries them (`hasPermission()` reads a
  per-user copy, so re-seeding roles alone would leave the Reviews page
  403'ing on an installed site).
- `bookings.amount` keeps its meaning — what the customer owes — so billing,
  invoices, receipts, agent commission and the ledger are untouched and simply
  bill the discounted sale. `gross_amount` records the list price.

**Fixed**
- The Coupons form escaped its own quotes (`class=\"text-danger\"`), emitting
  broken markup for every required-field marker and number input.

## 1.1.0 — 2026-09-10
Changes made in response to the CodeCanyon review of the 1.0.0 submission.

**Security**
- Removed `POST /api/v1/wallet/topup` — it credited a customer's wallet with
  no payment taken. A wallet can now only be funded by a real payment or a
  staff-recorded manual adjustment.
- Wallet and agent-withdrawal balance checks now run inside a database
  transaction with a row lock, closing a race that let two concurrent
  requests both pass a balance check and overdraw.
- Visa applicant documents (passport scans, etc.) moved from the public disk
  to a private one. Downloading now always goes through an authenticated,
  permission- and ownership-checked route for staff, customers and agents.
  Existing installs: run `php artisan flow:move-private-documents` once
  after upgrading to move any previously-uploaded documents off the public
  disk (safe to re-run).
- SVG uploads for the site logo and favicon are no longer accepted (stored
  XSS risk from inline SVG rendering).
- The image-upload pipeline no longer builds a function name from the
  uploaded file's extension and calls it; it now checks the extension
  against an explicit whitelist before processing.
- Android release builds now fail immediately with a clear message if
  `android/key.properties` is missing or incomplete, instead of silently
  falling back to a debug-signed build that the Play Store would reject.

**Fixed**
- Uploading a logo, favicon or any other file through an admin form behind the
  `XSS` middleware failed with "something went wrong": the middleware ran
  `strip_tags()` over every input including the uploaded files, coercing each
  one to a temp-path string. It now only touches string inputs, and the
  General Settings route it was meant to skip is spelled correctly.

**Fixed**
- The installer rejected the administrator email on servers without outbound
  DNS (the rule was `email:rfc,dns`); it now validates the address format only.

**Changed**
- Settings → API Security is seeded with a fixed default `X-App-Key`
  (`EnsureAppKey::DEFAULT_KEY`), matching the value compiled into the mobile
  app, so the app and a fresh backend pair up with no setup. Generate a new
  key in the panel to rotate; blank switches the check off.

**Logic**
- Booking cancellation now respects an admin-configurable window and
  penalty (*Settings → General Settings → Booking Policy*) instead of
  allowing a full refund at any time, including after the travel date.

**Performance**
- Wallet balance is read from the latest ledger row instead of loading a
  customer's full transaction history; new entries update the running
  balance in place instead of rewriting every prior row; the total held
  across all wallets is now one SQL aggregate instead of one query per
  customer.

**Dependencies**
- Removed the unused Inertia/Vue layer (46 Vue components, the Inertia
  middleware, `app.blade.php`) — nothing in the app rendered through it.
- Removed `laravel/breeze` (unused scaffolding) and `minimum-stability: dev`
  from composer.json.
- Upgraded `intervention/image` from the end-of-life 2.7 to 3.x.
- Standardised on one jQuery build (3.7.1) and one Select2 build (4.1)
  across the admin panel; Bootstrap intentionally kept at two builds
  (4 for the existing admin panel/auth pages, 5 for the public frontend) —
  a full markup port was judged too risky to do as part of this fix.

**Packaging**
- Compressed documentation screenshots (217 MB → 49 MB) and resynced the
  shipped offline documentation folder, which had gone stale.
- Replaced `online-documentation.html`'s blind redirect with a page that
  opens the included offline documentation first.
- Removed committed build output (`resources/scss/backend/*.css[.map]`,
  stray `.css.map` files), `.bak` files, `tinker_test.php`, the internal
  `issues.txt`, and Flutter/Gradle build caches from the working tree.
- Enabled R8 (`isMinifyEnabled`, `isShrinkResources`) for Android release
  builds and documented `--obfuscate --split-debug-info` in the app's
  README.
- Release packager now also strips runtime data under `storage/app`
  (uploaded documents, backups), generated `bootstrap/cache/*.php`, editor
  LSP cache files, source maps, internal planning docs and every empty
  directory, and asserts none of them survived. Untracked the stray
  `storage/framework/lsp-*.php` and `*.css.map` files from git.
- Replaced the stock Laravel README with one that covers install, modes,
  tests and the 1.0.x → 1.1.0 upgrade path; `.env.example` defaults now
  name the product instead of the starter template.

## 1.0.0 — Initial Release
- Tour packages, categories, schedules, guides and package bookings
- Visa management with appointments, documents, status tracking and expiry management
- Hajj & Umrah with pilgrims, hotel/flight allocation, groups, payments and document verification
- Hotel, flight and transport booking modules with vouchers, ticketing, reissue, cancellation and refunds
- Additional services — travel insurance, student consultancy, medical tourism, corporate travel and events
- CRM with leads, activities, follow-up calendar, notes and communication history
- Double-entry accounting with full statements, invoices, receipts, refunds and tax reports
- Suppliers, contracts, supplier ledger and agent commission with agent invoices
- Task management, support center, knowledge base and nine exportable reports
- CMS website builder, SEO settings, HR module and role-based access control
- Customer, Agent and Staff self-service portals
- Optional multi-tenant SaaS platform with plans, subscriptions, self-serve signup and 12 payment gateways
- Firebase push notifications (FCM HTTP v1), switched on from the admin panel
- Gmail API mail driver for hosts that block outbound SMTP ports, alongside SMTP and Sendmail
- Optional shared app key (X-App-Key) for the mobile API, generated and rotated from the admin panel
- Guided web installer with a system check, database test and one-click install
- Unified email verification codes across web and mobile, with expiry and rate limiting
- Four languages out of the box — English, Bangla, Arabic and Hebrew — with RTL/LTR support
