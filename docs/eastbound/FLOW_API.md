# FLOW API Discovery

## API Structure

FLOW exposes a versioned mobile API under `/api/v1` in `routes/api.php`.

Response convention is documented in the route file as:

```text
{ success, message, data }
```

The convention is implemented by API traits such as `ApiReturnFormatTrait` and transform helpers.

## API Middleware and Authentication

- API middleware group:
  - `EnsureAppKey`
  - `throttle:api`
  - route bindings
- Public credential endpoints are throttled with `api-auth` and `api-otp` limiters.
- Protected routes use `auth:sanctum`.
- `EnsureAppKey` checks an `X-App-Key` header when a key is configured in settings or env.
- Same-origin web frontend requests can be exempt from `X-App-Key`.
- Sanctum tokens are used for both `User` and `Customer` authenticatable models.

## Public API Capabilities

Public endpoints include:

- Auth:
  - register
  - login
  - OTP request/verify
  - reset password
  - mobile social login
  - social providers
- Public browsing:
  - home
  - tours
  - hotels
  - categories
  - hajj packages
  - transport types, vehicle categories, services
  - flight routes and live flights
  - package reviews
  - visa services and tracking
- Public content:
  - FAQs
  - content blocks
  - testimonials
  - become-agent lead submission
  - enquiry types
  - enquiries
  - contact info
  - contact submission
- Settings/localization:
  - app settings
  - app languages
  - language terms

## Protected Customer API Capabilities

Sanctum-protected customer/account endpoints include:

- auth/me and logout
- dashboard
- package bookings: list, create, quote, show, pay, cancel
- hotel bookings: create/list/pay
- flight requests/list/trip plans/live growth features
- transport list/create/show/pay/cancel
- passports
- documents
- invoices/payments
- PDF downloads: invoice, receipt, hotel voucher, e-ticket
- hajj registrations
- wallet
- visa applications/documents
- profile/avatar/password/preferences
- travelers
- notifications
- device tokens
- support tickets
- guide rating
- reviews

## Agent API Capabilities

Agent endpoints under `/api/v1/agent` include:

- dashboard
- wallet and withdrawal
- commissions
- bookings list/create/show/update/cancel
- hotel bookings
- transport bookings
- flight bookings
- customers
- invoices
- transactions
- reports
- support

## Guide API Capabilities

Guide endpoints:

- assignments
- start assignment
- complete assignment

## API Resources/Serialization

No heavy Laravel API Resource layer was observed in the initial route/controller inventory. Controllers appear to use direct arrays/traits/transform helpers. EastBound APIs should match this convention unless a local controller already uses a resource class for the target domain.

## External Website Lead Intake Recommendation

EastBound needs external websites to submit leads. The safest FLOW-compatible approach is:

1. Add a new `/api/v1` endpoint under the existing API architecture, not a separate API style.
2. Reuse `Lead` and `LeadRepository` or a new lead-intake service.
3. Preserve `{success, message, data}` response shape.
4. Use existing `EnsureAppKey` or create a scoped API key mechanism that follows settings architecture.
5. Add throttle limits comparable to auth/enquiry endpoints.
6. Validate with a FormRequest.
7. Store raw attribution fields additively on `leads` or a related `lead_attributions` table.
8. Emit a `crm_activities` entry for the first inbound touch if useful.

Do not introduce a separate JSON API framework or bypass FLOW auth/settings/rate-limit conventions.

## Webhooks

Payment callbacks exist for online payments. No generic webhook framework was observed. If EastBound requires webhooks, add them as a narrowly scoped API/webhook module with:

- signature verification
- throttling
- audit logging
- idempotency keys
- additive route group

## API Gaps for EastBound

- No public lead endpoint with full UTM/source/campaign attribution.
- No opportunity/quotation/sales-order/trip API.
- No vendor/purchasing API.
- No trip operations API.
- No generic webhook ingestion.

## Backward Compatibility Requirements

Future API changes must preserve:

- Existing `/api/v1` routes.
- Existing response envelope.
- Existing Sanctum customer/agent auth flows.
- Existing app key behavior.
- Existing mobile routes for bookings, hotels, transport, flights, visa, wallet, documents, invoices, reviews, support.
