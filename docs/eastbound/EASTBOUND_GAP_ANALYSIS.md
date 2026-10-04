# EastBound Gap Analysis

## Summary

FLOW already contains more than a booking website. It has CRM leads, campaigns, customers, package bookings, service bookings, suppliers, accounting, agent settlement, tasks, reports, mobile API, public CMS, and SaaS/installer modules.

EastBound should therefore extend FLOW rather than build a parallel ERP. The largest gaps are not basic CRUD; they are lifecycle traceability and cross-module orchestration:

```text
Website/Campaign -> Lead -> Customer -> Opportunity -> Quotation
-> Accepted Quotation -> Sales Order/Confirmed Booking -> Trip
-> Trip Services -> Reservations/Operations -> Vendor Purchasing
-> Customer & Vendor Accounting -> Trip Profitability
```

## Already Strong Areas

- Customers/travelers/passports/documents.
- Leads and CRM activities.
- Package catalogue and package booking.
- Hotel, transport, flight, visa, Hajj/Umrah, event service records.
- Supplier master, contracts, and supplier ledger.
- Customer invoice/receipt/refund flow.
- Agent commissions/settlement.
- Tasks and support.
- Public CMS and mobile API.
- PDF infrastructure.
- Settings/localization/admin UI conventions.

## Major Gaps

### 1. Opportunity and Sales Pipeline

FLOW leads have a `stage`, but there is no separate opportunity object. EastBound needs opportunity lifecycle, probability/value, sales owner/team, expected close date, and relation to quotations.

Recommendation: add an Opportunity module linked to `leads`, `customers`, and `users`.

Classification: C.

### 2. Quotation/Proposal

No quotation/proposal object exists. Packages/bookings are direct sales records and cannot safely represent draft quotation revisions.

Recommendation: add Quotation, QuotationRevision, QuotationItem, and approval/acceptance workflow modules. Reuse packages/hotels/transport/guides/suppliers as lookup/master data.

Classification: C with B integrations.

### 3. Confirmed Sales Order and Trip

`bookings` is package/tour booking. `trip_plans` is an AI planning artifact. Neither is a safe generic DMC trip operations container.

Recommendation: add a Trip/Sales Order module linked to accepted quotation and optionally to existing `bookings`. Do not rename `bookings`.

Classification: C/B.

### 4. Trip Services and Operations

FLOW has separate service booking tables but no generic service layer tying hotel/transport/flight/guide/visa/activity reservations into one trip.

Recommendation: add Trip Service wrapper records that can polymorphically link to existing service tables and carry operations status, deadlines, notes, supplier, cost, and confirmation data.

Classification: C with B reuse.

### 5. Vendor Purchasing and Trip Costing

Supplier master/contracts/ledger exist, but there is no trip-service purchase order/cost allocation.

Recommendation: add vendor purchase/cost allocation module linked to suppliers, contracts, trip services, and supplier transactions.

Classification: C/B.

### 6. Trip Profitability

FLOW has revenue/accounting reports but no per-trip profitability model combining customer revenue, item selling prices, supplier costs, commissions, refunds, and adjustments.

Recommendation: add profitability service/report after trip service and purchasing foundations exist.

Classification: C.

### 7. Lead Attribution and Intake

Leads have source and campaigns exist, but UTM, website, response time, and assignment automation are missing.

Recommendation: extend leads with attribution table/columns and add lead intake service/API.

Classification: B.

### 8. Multi-Currency and Exchange Rates

Currency master exists, but booking/invoice/service rows generally store decimal amounts without transaction currency/exchange rate.

Recommendation: if EastBound needs multi-currency quotation/accounting, add currency/exchange-rate fields to new quotation/trip-cost tables first. Avoid retrofitting every existing FLOW table unless required.

Classification: C now; D only if full legacy accounting conversion is required.

## CRM Requirement Gaps

| Requirement | Status | Recommended path |
|---|---|---|
| Websites | Partial via CMS | Extend CMS/campaign attribution. |
| Lead sources | Partial enum/string | Normalize additively. |
| Campaigns | Exists | Extend with UTM/source relation. |
| Leads/enquiries | Exists | Reuse `leads`; do not duplicate. |
| Lead statuses | Partial fixed stage values | Extend/configure carefully. |
| Assignment | Exists via `assigned_to` | Reuse. |
| Sales teams | Missing | New team module. |
| Activities/follow-ups | Partial via `crm_activities` and `tasks` | Extend. |
| Communication logs | Partial | Use/extend `crm_activities`. |
| Round-robin | Missing | New assignment service. |
| Response time tracking | Missing | Add lead timestamps/activity logic. |
| Sales pipeline | Partial via lead stage | Add opportunities. |

## Sales Requirement Gaps

| Requirement | Status | Recommended path |
|---|---|---|
| Packages/templates | Exists/partial | Reuse packages; add template/version semantics if needed. |
| Quotations/proposals | Missing | New quotation module. |
| Quotation items | Missing | New quotation item module. |
| Hotels/transport/tours/guides in quote | Master data exists | Reuse as selectable item sources. |
| Supplier cost/selling price/markup | Partial | New item-level pricing/cost fields. |
| Discounts | Booking discounts exist | Add quotation-level and item-level discount rules. |
| Gross profit/margin | Missing | Add calculation service. |
| Quotation PDF | PDF service exists | Extend DocumentService. |
| Revisions/approval/customer acceptance | Missing | New workflow/history tables. |

## Booking/Trip Gap

FLOW terms:

- `Booking`: primarily package/tour booking with customer/package/date/travelers/amount/status.
- `HotelBooking`: accommodation service sale/reservation.
- `TransportBooking`: transport service sale/reservation.
- `FlightBooking`: flight request/ticket.
- `EventBooking`: event/activity sale.
- `TripPlan`: generated planning suggestion, not confirmed operations trip.

EastBound terms:

- Confirmed Booking/Sales Order: accepted commercial order.
- Trip: operational container.
- Trip Services: individual hotel/transport/tour/guide/etc. services.

Recommendation:

- Do not redefine existing `bookings`.
- Add Trip/Sales Order layer that can relate to `bookings` and service bookings.
- Use wrapper/link tables for service orchestration.

## Operations Gap

Existing operations are service-specific. EastBound needs a generalized operations layer:

- service confirmation state
- reservation deadlines
- supplier booking references
- attachments
- comments/internal notes
- operational alerts
- service-level task generation

Use `tasks` and service booking tables where possible, but add a trip-service operations model.

## Supplier/Vendor Gap

Use `suppliers` as the vendor base. Do not create another vendor master.

Needed additions:

- vendor categories/tags if `type` is insufficient
- vendor contacts
- vendor documents/compliance
- trip-service purchase/cost allocation
- purchase order/reservation references

## Financial Gap

Reusable:

- invoices
- receipts
- refunds
- account transactions
- supplier transactions
- agent commissions
- payment intents

Missing:

- quotation financial model
- trip cost accrual
- vendor bill allocation to trip service
- profitability report
- exchange rate support

## D-Class Core Changes

No EastBound requirement currently appears to require unavoidable destructive FLOW core changes.

Potential future D only if approved:

1. Full legacy `bookings` table conversion into a generic trip/order table.
   - Not recommended.
   - Safer alternative: add trip/order tables related to bookings.
2. Full multi-currency retrofit across all existing accounting and booking tables.
   - Not recommended initially.
   - Safer alternative: implement currency on new quotation/trip cost layers first.
3. Replacing RBAC with a new permission package.
   - Not recommended.
   - Safer alternative: add permissions to existing system.

## Highest-Risk Integration Areas

- Accounting side effects and idempotency.
- Booking versus Trip semantics.
- Supplier cost allocation and payable posting.
- Customer/agent/staff auth boundary.
- Public lead API spam/security.
- Sensitive document storage.
- Existing mobile API backward compatibility.
- SaaS/tenant mode if enabled.
