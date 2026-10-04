# FLOW Existing Business Workflows

## Public Package Booking

Observed path:

1. Visitor opens `tour-packages` or `tour-packages/{id}`.
2. Package data comes from `packages`, `package_itineraries`, reviews, wishlist state, and guide assignment.
3. Visitor submits `tour-packages/{id}/book`.
4. `FrontendController::bookStore()` validates customer details, travel date, travelers, optional coupon, optional visa need.
5. `ResolvesCustomer` resolves/creates a `customers` row.
6. `BookingPricing` calculates gross/net amount, coupon, and discount columns.
7. A `bookings` row is created with `status = pending`.
8. If visa is requested, `OpenVisaCaseForBooking` opens a visa case.
9. On later status changes, `BookingObserver` and `BillingService` handle invoice/receipt side effects:
   - `confirmed` or `paid` raises/updates invoice.
   - `paid` records receipt for outstanding amount.
   - `cancelled` withdraws unpaid invoice or refund logic applies.

Semantics:

- This is a real package booking, not merely an enquiry.
- `bookings` is package/tour centered.

## Generic Public Booking/Enquiry Forms

Observed path:

1. Public route `book/{type}` opens a type-driven form.
2. Submission goes to `FrontendController::bookingStore()`.
3. Depending on type, actions can create service records or leads.
4. Comments in routes say each request creates a CRM Lead, but code imports service-opening actions, so this path must be checked per type before changing it.

EastBound caution:

- Do not assume all public requests are raw leads. Some create operational records directly.

## Become Agent

Observed path:

1. Visitor opens `become-an-agent`.
2. Submission goes to `FrontendController::becomeAgentStore()` and API `ContentController::becomeAgent()`.
3. This creates a CRM lead rather than a supplier/agent user directly.

EastBound mapping:

- Reuse lead source/campaign attribution, then convert only through approved workflows.

## Lead to Flight Booking

Observed path:

1. Admin opens `crm/leads/{id}`.
2. Admin chooses flight conversion route.
3. `LeadController::convertFlight()` parses `From`, `To`, and `Departure Date` lines from lead notes.
4. It pre-fills a `FlightBooking` object for the conversion form.
5. `LeadController::storeFlightBooking()` validates PNR, passenger, airline, route, date, fare, status.
6. A `flight_bookings` row is created.
7. The lead is marked `Won`, notes are appended, and a `crm_activities` timeline entry is created.

Semantics:

- Lead conversion is real and implemented for flight bookings.
- It is not a generalized opportunity/quotation pipeline.

## Lead to Hotel Booking

Observed path:

1. Admin opens hotel conversion from lead.
2. `LeadController::convertHotel()` parses check-in/check-out from notes.
3. Admin selects hotel/room and confirms booking fields.
4. `LeadController::storeHotelBooking()` creates `hotel_bookings`.
5. Lead is marked `Won` and a CRM activity is logged.

Semantics:

- Existing CRM has specific conversion paths for flight and hotel.
- There is no observed generic quote/order conversion.

## Admin Package Booking

Observed path:

1. Admin opens `bookings/create`.
2. `BookingController::create()` loads active packages, active customers, and users with Agent role.
3. `BookingRepository::store()` calculates booking pricing/coupon columns.
4. A `bookings` row is created.
5. Observers sync billing and commissions where status/agent conditions apply.

Semantics:

- Admin package booking is sale/order-like but still a single package booking.

## Customer Portal Booking

Observed portal routes:

- `portal/customer/tour-bookings`
- `portal/customer/tour-bookings/book`
- `portal/customer/tour-bookings/quote`
- `portal/customer/hotel-bookings/book`
- `portal/customer/transport-bookings/request`
- payment routes for hotels/transport

Semantics:

- Customer portal can initiate tour, hotel, and transport sales.
- The portal reuses existing booking/service booking tables.

## Agent Portal Sales

Observed API and portal routes:

- Agent package bookings: `api/v1/agent/bookings`, portal agent booking views.
- Agent hotel bookings: `api/v1/agent/hotel-bookings`.
- Agent transport bookings: `api/v1/agent/transport-bookings`.
- Agent flight requests: `api/v1/agent/flight-bookings`.
- Agent finance: commissions, wallet, invoices, withdrawals, reports.

Semantics:

- Agents are `users` with Agent role and `agent_id` attribution on sales/service rows.
- Agent commission is observer/service-driven and should not be bypassed.

## Hotel Booking Workflow

Observed entities:

- `hotels`
- `hotel_rooms`
- `hotel_bookings`
- invoices via polymorphic source
- hotel voucher PDF

Status/billing semantics:

- `Confirmed` or `Paid` hotel bookings are billable.
- `Paid` records payment.
- `Cancelled` withdraws unpaid invoice.

EastBound mapping:

- Good candidate for Trip Service/Reservation reuse, but needs a trip/service wrapper.

## Transport Booking Workflow

Observed entities:

- `transport_services`
- `vehicle_categories`
- `drivers`
- `transport_bookings`
- transport reports and category/type pages

Status/billing semantics:

- `Confirmed`, `Paid`, or `Completed` transport bookings are billable.
- `Paid` records payment.
- `Cancelled` withdraws unpaid invoice.

EastBound mapping:

- Reuse for transfer/transport services and driver assignment.

## Flight Booking Workflow

Observed entities:

- `flight_routes`
- `flight_bookings`
- flight search service for live flights

Status semantics:

- Flight status transitions are defined on `FlightBooking::TRANSITIONS`.
- Billing occurs when a ticket number and fare exist and status is not cancelled/refunded.
- Cancellation/refund/reissue views exist.

EastBound mapping:

- Reuse for flight services, not as generic trip/order.

## Visa Workflow

Observed entities:

- `visa_services`
- `visa_applications`
- `visa_documents`

Workflow:

- Public/API/customer submissions can open visa cases.
- Admin has dashboard, applications, tracking, expiry, appointment, document screens.
- Documents are sensitive; privacy tests exist and `MovePrivateDocuments` command indicates prior hardening.

EastBound mapping:

- Reuse for visa trip services and document operations.

## Hajj/Umrah Workflow

Observed entities:

- `hajj_packages`
- `hajj_pilgrims`
- `hajj_groups`
- `hajj_flights`

Workflow:

- Hajj/Umrah packages and registrations.
- Pilgrim allocation to groups, hotels, flights, seats.
- Payment amount paid/due syncs to invoices/receipts through observer.

EastBound mapping:

- Reuse for Hajj/Umrah verticals. Do not generalize it as the main DMC trip model.

## Supplier Workflow

Observed path:

1. Admin manages suppliers under `supplier/manage`.
2. Admin manages supplier contracts under `supplier/contracts`.
3. Supplier ledger rows are created under `supplier/ledger`.
4. `SupplierTransactionObserver` and `SupplierAccountingService` recalculate payable balances and accounting effects.

Semantics:

- This is a real vendor/payable subsystem.
- It is not yet linked deeply to trip services or per-trip profitability.

## Accounting Workflow

Observed automated posting:

- Booking/service status change creates/updates invoices and receipts.
- Receipts post to ledger and update invoice paid amount.
- Refunds post through billing/ledger.
- Wallet entries update balances.
- Agent commission and withdrawal flows post through settlement service.
- Supplier transactions update supplier balance and accounting.

EastBound caution:

- Direct writes to balances or invoices will break accounting consistency.
- Future trip profitability must integrate with accounting services/observers.

## Reporting Workflow

Existing report routes:

- `reports-center/sales`
- `reports-center/financial`
- `reports-center/customer`
- `reports-center/package`
- `reports-center/hotel`
- `reports-center/flight`
- `reports-center/visa`
- `reports-center/agent`
- `reports-center/custom`

Semantics:

- Reporting exists as Blade/admin pages with repository/controller queries.
- EastBound analytics should extend report center rather than create a parallel dashboard.
