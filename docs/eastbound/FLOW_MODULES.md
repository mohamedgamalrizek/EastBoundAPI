# FLOW Module and Admin Inventory

## Existing Functional Modules

| Module | Primary files | Existing capability | EastBound reuse |
|---|---|---|---|
| Dashboard | `DashboardController`, `backend/dashboard.blade.php` | Admin overview | Extend with EastBound KPIs later. |
| User/RBAC | `UserController`, `RoleController`, `routes/user.php`, `users`, `roles`, `permissions` | Users, roles, permissions | Reuse. |
| Settings | `SettingsController`, `routes/setting.php`, `settings` | General, appearance, mail, SMS, recaptcha, payment, social login, API security, push, integrations | Reuse. Add EastBound settings in same architecture. |
| CRM Leads | `LeadController`, `LeadRepository`, `Lead`, CRM views | Leads, lead stages, assignment field, conversion to flight/hotel, activity timeline | Extend. |
| CRM Activities | `CrmActivityController`, `CrmActivityRepository`, `crm_activities` | Follow-up/activity records | Extend. |
| Contact Messages | `ContactMessageController`, `contact_messages` | Public contact messages | Reuse as inbound source. |
| Campaigns | `CampaignController`, `campaigns` | Campaign CRUD | Extend for UTM/source attribution. |
| Customers | `CustomerController`, `CustomerPortalController`, `Customer` | Customer CRUD, wallet adjustments, profile, customer portal | Reuse. |
| Travelers/Passports/Documents | `TravelerController`, `PassportController`, `DocumentController` | Traveler and document management | Reuse. |
| Packages | `PackageController`, `PackageRepository`, `Package` | Package catalogue, pricing, public package pages, bookings | Reuse as package templates/product catalogue. |
| Tours | `TourController`, `TourScheduleController`, guides/assignments | Package categories, schedules, guides, reports | Reuse for guide/schedule functions. |
| Bookings | `BookingController`, `BookingRepository`, `Booking` | Package bookings, discounts, coupons, billing, commissions | Extend/relate to EastBound sales order/trip. |
| Hotels | `HotelController`, `HotelRoomController`, `HotelBookingController` | Hotels, rooms, bookings, vouchers, reports | Reuse for accommodation services. |
| Transport | `TransportController`, `DriverController`, `VehicleCategoryController`, `TransportServiceController` | Transport bookings, drivers, categories, transport catalogue | Reuse for transfer/transport services. |
| Flights | `FlightController`, `FlightRouteController` | Flight routes, requests/tickets, cancellation/reissue/refund | Reuse for flight services. |
| Visa | `VisaController`, `VisaServiceController` | Visa services, applications, appointments, documents, tracking | Reuse for visa services. |
| Hajj/Umrah | `HajjController`, `HajjPilgrimController` | Packages, pilgrims, groups, flights, allocation, payments/reports | Reuse for that vertical only. |
| Event Tours | `EventTourController`, `EventBookingController` | Event tours/bookings | Reuse for event/activity services where appropriate. |
| Suppliers | `SupplierController`, `SupplierRepository`, supplier models | Supplier master, contracts, ledger, reports | Extend for EastBound vendor management and purchasing. |
| Accounting | `AccountingController`, `InvoiceController`, `ReceiptController`, `AccountTransactionController`, accounting services | COA, journal, invoice, receipt, refunds, financial reports | Reuse. |
| Agent Finance/Portal | Agent controllers/repositories, settlement service | B2B agent sales, commissions, wallets, invoices, withdrawals | Reuse for B2B agents; keep distinct from internal sales reps. |
| Tasks | `TaskController`, `TaskRepository`, `Task` | Tasks, assignments, calendar, deadlines, kanban, projects | Extend for operations tasks with trip/service links. |
| Support/KB/Announcements | Support controllers/repositories | Tickets, support KB, announcements | Reuse. |
| Reports | `ReportController`, `ReportRepository`, report views | Sales, financial, customer, package, hotel, flight, visa, agent, custom reports | Extend. |
| CMS/Public Website | `FrontendController`, CMS/blog/menu/slider/content controllers | Public content, landing pages, packages, blogs, menus | Reuse. |
| Payments | `PaymentIntentService`, gateways, callback controller | Online payment initiation/callback for payable models | Reuse. |
| Documents/PDF | `DocumentService`, `pdf` views | Invoice, receipt, hotel voucher, e-ticket PDFs | Extend for quotation/trip docs. |
| Installer | `Modules/Installer` | Installation wizard/health | Preserve. |
| SaaS | `Modules/Saas` | Tenants, plans, subscriptions, platform payments | Preserve; separate from agency ERP logic. |

## Existing Admin Navigation Structure

The actual sidebar is rendered from Blade partials under `resources/views/backend/partials`. Route inventory and view folders show these admin sections:

- Dashboard
- CRM: leads, contact messages, job applications, follow-up calendar, activity timeline, notes, communication history, CRM activities
- Package/tour management
- Booking management
- Customer management
- Visa management
- Hajj/Umrah management
- Hotel management
- Flight management
- Transport management
- Supplier management
- Accounting
- Agent finance
- HR/staff portal
- Support/KB/announcements
- Tasks/projects/calendar/kanban/deadlines
- CMS/content/blog/menu/slider/gallery/FAQ/testimonials/services
- Reports center
- Settings
- SaaS panel when enabled

## Reusable UI Patterns

Future EastBound screens should reuse these patterns:

- Page shell: `backend.partials.master`.
- Data tables: `x-data-table` and existing table markup in index views.
- Analytics/KPI cards: `x-list-analytics`, module list analytics patterns.
- Forms: `_form.blade.php` partials in module folders.
- Delete actions: `backend.partials.delete-ajax`.
- Modals: `backend.partials.dynamic_modal`.
- Badges: model methods such as `statusBadge()` and CSS classes like `bullet-badge-*`.
- File/image upload: `components/file-uploader`, `backend/components/image-field`.
- Phone input: `components/phone-input`.
- Filters: report `_filterbar.blade.php` and index filter patterns.
- Charts: existing list analytics/dashboard chart structures.
- Sidebars for admin/agent/customer/staff.

## Where Business Logic Lives by Module

- Controllers typically orchestrate request validation and views.
- Repositories encapsulate basic CRUD and common data assembly.
- Services encapsulate nontrivial business rules.
- Observers enforce side effects from model writes.
- Request classes validate admin/portal/API inputs.

For EastBound, follow this local pattern:

1. Controller for route/UI orchestration.
2. Request class for validation.
3. Repository for CRUD/listing if matching existing modules.
4. Service for workflow-heavy business rules.
5. Observer/event only when side effects must apply to every write path.

## Existing CRM-Like Features

Already present:

- Leads with `source`, `stage`, `assigned_to`, `owner`, `value`, notes.
- CRM activities linked to leads/customers/users.
- Contact messages.
- Campaigns.
- Job applications.
- Follow-up/activity/notes/communication pages exist, but some are dummy/static per route comments.
- Lead conversion to flight/hotel bookings.

Missing/partial:

- Website/campaign/UTM attribution fields on leads.
- Sales teams as first-class entities.
- Round-robin assignment.
- Response time tracking.
- Opportunity object separate from lead.
- Communication logs beyond generic CRM activities.
- Configurable lead statuses.

## Existing Sales Features

Already present:

- Packages as sellable catalogue.
- Package itinerary.
- Booking pricing, coupons, discounts, points.
- Package booking creation from public/admin/customer/agent contexts.
- Invoices/receipts for confirmed/paid bookings.
- PDF generation infrastructure.

Missing/partial:

- Quotation/proposal object.
- Quotation line items for hotel/transport/tour/guide/other services.
- Supplier cost versus selling price and margin at line-item level.
- Quotation PDF/revisions/approval/customer acceptance.
- Sales order distinct from booking/trip.

## Existing Operations Features

Already present:

- Tasks with assignment, priority, due dates, calendar/kanban/deadlines views.
- Hotel booking status and vouchers.
- Transport booking status and driver assignment.
- Flight booking status transitions/reissue/refund/cancellation views.
- Guide assignment to package/schedule.
- Visa document and appointment tracking.
- Hajj allocation to groups/hotels/flights/seats.
- Support tickets, documents, notes/activity fields.

Missing/partial:

- Generic reservation task model linked to trip service.
- Operations deadline templates.
- Confirmation tracking per service supplier.
- Internal comments/attachments per trip service.
- Operational alert rules.

## Existing Vendor Features

Already present:

- Suppliers with type/contact/balance/status.
- Supplier contracts with rates, commission, credit days, terms, document, status.
- Supplier ledger with bill/payment/credit/adjustment and payable balance.
- Specialized hotel/driver/guide master data.

Missing/partial:

- Unified vendor profile across hotels, drivers, guides, transport providers.
- Purchase order or supplier reservation tied to trip services.
- Vendor document/compliance tracking.
- Trip-level vendor cost accrual/profitability.

## Existing Accounting Features

Already present:

- Chart of accounts.
- Double-entry-like account transactions with source morph.
- Invoices, receipts, refunds.
- Customer wallet.
- Agent commissions/wallet/withdrawals/invoices.
- Supplier transactions and balances.
- Financial reports: ledger, journal, cash/bank book, income, expenses, trial balance, balance sheet, P&L, tax/refunds.
- Payment methods and online payment gateways.

Missing/partial:

- Multi-currency per transaction/exchange rates.
- Trip-level cost/revenue/margin model.
- Supplier invoices/purchase orders tied to specific trip services.
- Tax model beyond reporting placeholders.
