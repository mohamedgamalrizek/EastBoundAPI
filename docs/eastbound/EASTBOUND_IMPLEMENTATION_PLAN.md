# EastBound Implementation Plan

## Guardrails

- Do not rebuild FLOW.
- Do not replace Laravel/Blade/repository architecture.
- Do not rename existing tables/models/routes/controllers.
- Do not change booking/accounting behavior without explicit approval.
- Add new functionality through additive migrations, models, services, controllers, routes, permissions, and views.
- Extend existing entities through nullable columns or related tables.

## Baseline Before Phase A

Current baseline:

- `composer validate --no-check-publish`: passed.
- PHP syntax check: passed, with deprecation warnings.
- `php artisan test`: failed due to unavailable/blocked MySQL connection to `127.0.0.1:3306`, database `flow`.
- `npm run build`: failed because `sass` command is missing.

Before implementation begins:

1. Configure a working test database or `.env.testing`.
2. Install/restore npm dependencies so `sass` is available.
3. Rerun full baseline.
4. Confirm repository root/version control state.

## Phase 0: Foundation and Safety

Purpose: create the minimum extension foundation without business behavior changes.

Work:

- Add EastBound permission seeds using FLOW permission conventions.
- Add EastBound navigation placeholders only when approved.
- Add service/provider bindings for future modules.
- Add shared polymorphic comments/attachments only if required by multiple modules.
- Establish test fixtures/factories for FLOW core entities.

Reuse:

- Existing `users`, `roles`, `permissions`.
- Existing layout/components/sidebar conventions.
- Existing settings/localization conventions.

Risk: Low.

## Phase A: Lead Intake and Attribution

Purpose: safely capture external website/campaign leads into FLOW CRM.

Work:

- Extend `leads` through related `lead_attributions` table or additive columns.
- Add FLOW-style `/api/v1` lead intake endpoint with `X-App-Key`/throttle/validation.
- Add campaign/source linkage.
- Add optional assignment service using `assigned_to`.
- Log first inbound activity in `crm_activities`.

Reuse:

- `leads`
- `campaigns`
- `crm_activities`
- `users`
- existing API envelope

Risk: Medium because endpoint is public-facing.

## Phase B: CRM Pipeline Extensions

Purpose: bridge leads to sales pipeline without duplicating leads.

Work:

- Add sales teams if needed.
- Add opportunities linked to leads/customers/users.
- Add opportunity stages/probability/value/expected close.
- Add follow-up/response-time fields or services.

Reuse:

- `leads.assigned_to`
- `customers`
- `crm_activities`
- `tasks`

Risk: Medium.

## Phase C: Quotations and Proposal Workflow

Purpose: create the missing sales quotation layer.

Work:

- Add quotation, quotation revision, quotation item tables.
- Reuse master data lookups: packages, hotels, rooms, transport services, guides, suppliers.
- Add item-level selling price, supplier cost, markup, discount, gross profit/margin.
- Add quotation statuses, approval, and customer acceptance.
- Extend `DocumentService` with quotation PDF.

Reuse:

- Package/hotel/transport/guide/supplier masters.
- Existing PDF style.
- Existing admin form/table components.

Risk: High because financial calculations start here.

## Phase D: Sales Order and Trip Container

Purpose: convert accepted quotations into operational records without redefining existing bookings.

Work:

- Add sales order/confirmed booking table linked to quotation/customer.
- Add trip table as operational container.
- Optionally create/link a FLOW `booking` where package booking semantics apply.
- Preserve existing `bookings`, `hotel_bookings`, `transport_bookings`, etc.

Reuse:

- `bookings` for package/tour sale compatibility.
- Existing customer and accounting references.

Risk: High due to semantics and backward compatibility.

## Phase E: Trip Services and Operations

Purpose: orchestrate services in one trip.

Work:

- Add trip service wrapper records.
- Link trip services to existing service rows: hotel booking, transport booking, flight booking, visa application, event booking, guide assignment.
- Add operations status, confirmation number, deadline, supplier, notes.
- Generate/link FLOW `tasks` for operational work.

Reuse:

- Existing service booking tables.
- `tasks`
- `tour_guides`, `drivers`, `visa_documents`.

Risk: High.

## Phase F: Vendor Purchasing

Purpose: add purchasing/cost control without duplicating supplier master.

Work:

- Extend supplier profiles if needed.
- Add purchase order/vendor reservation/cost allocation tied to trip services.
- Integrate with `supplier_transactions` through service layer.
- Add supplier document/compliance if needed.

Reuse:

- `suppliers`
- `supplier_contracts`
- `supplier_transactions`
- `SupplierAccountingService`

Risk: High due to payables/accounting.

## Phase G: Customer and Vendor Accounting Integration

Purpose: connect new sales/trips/purchases to existing accounting safely.

Work:

- Map accepted quotation/order totals to invoices through `BillingService` or new companion service.
- Ensure receipts/refunds still post through current services.
- Add vendor payable posting from purchase/cost allocations.
- Add idempotent source references for all ledger postings.

Reuse:

- `invoices`
- `receipts`
- `refunds`
- `accounts`
- `account_transactions`
- `supplier_transactions`

Risk: Very high.

## Phase H: Trip Profitability and Reports

Purpose: deliver EastBound traceability and profit visibility.

Work:

- Add profitability service aggregating:
  - quotation/order revenue
  - invoices/receipts/refunds
  - supplier costs/payments
  - discounts
  - commissions
  - adjustments
- Add report center pages for trip profitability, sales pipeline, conversion, operations deadlines, vendor exposure.

Reuse:

- Report center architecture.
- Accounting reports/components.

Risk: Medium/high depending on accounting completeness.

## Phase I: API and Portal Extensions

Purpose: expose EastBound features after internal workflows stabilize.

Work:

- Add API endpoints only after admin modules are stable.
- Add customer portal quotation acceptance if needed.
- Add operations/vendor mobile endpoints only if there is a real app requirement.

Reuse:

- `/api/v1` route conventions.
- Sanctum.
- Existing response envelope.

Risk: Medium.

## Testing Strategy

For each phase:

- Feature tests for route permissions.
- Model/service tests for calculations.
- Regression tests for existing FLOW bookings, invoices, receipts, package pages, API auth, and mobile booking endpoints.
- Accounting idempotency tests where financial posting exists.
- API throttling/security tests for public endpoints.
- PDF render tests where documents are generated.

## Do Not Start Automatically

This plan is intentionally not Phase A implementation. Implementation should start only after review and approval.
