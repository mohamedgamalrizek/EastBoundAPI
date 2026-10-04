# FLOW Database Inventory

## Database Architecture Summary

FLOW uses Laravel migrations as the schema source of truth. There are 139 root migrations plus first-party module migrations under `Modules/Installer` and `Modules/Saas`.

The schema is a broad ERP/travel-agency schema with these major domains:

- Identity/RBAC: `users`, `roles`, `permissions`, `customers`, `personal_access_tokens`, social login.
- CRM/marketing: `leads`, `crm_activities`, `contact_messages`, `campaigns`, `subscribers`, `job_applications`.
- Travel products: `packages`, `package_categories`, `package_itineraries`, `tour_schedules`, `hotels`, `hotel_rooms`, `transport_services`, `flight_routes`, `visa_services`, `hajj_packages`, `event_tours`.
- Bookings/services: `bookings`, `hotel_bookings`, `transport_bookings`, `flight_bookings`, `visa_applications`, `hajj_pilgrims`, `event_bookings`, side-service tables.
- Operations: `tasks`, `tour_guides`, `tour_guide_assignments`, `drivers`, support/tickets, documents.
- Supplier/accounting: `suppliers`, `supplier_contracts`, `supplier_transactions`, `accounts`, `account_transactions`, `invoices`, `receipts`, `refunds`, wallets, commissions.
- CMS/site: `cms_pages`, `blogs`, `menus`, `sliders`, `content_blocks`, `galleries`, `faqs`, `testimonials`, `social_links`.
- SaaS/platform: `tenants`, `domains`, `plans`, `subscriptions`, `payments`.

## Important Tables

| Table | Purpose | Model | Important relationships/columns | EastBound reuse |
|---|---|---|---|---|
| `users` | Staff/admin/agent/customer portal login accounts | `App\Models\User` | `role_id`, `customer_id`, `permissions`, `preferences`, `commission_rate`, `status`, `image_id`, `nid` | Reuse for staff, sales reps, operations, agents. Extend with related profile tables if needed. |
| `roles` | Named roles | `App\Models\Role` | `name`, `slug`, `permissions`, `status` | Reuse. Add EastBound permissions/roles additively. |
| `permissions` | Permission definitions/keywords | `App\Models\Permission` | `attribute`, `keywords` | Reuse. |
| `customers` | Customer business record and API-authenticatable customer account | `App\Models\Customer` | `name`, `email`, `phone`, `tier`, `status`, `preferences`, `referral_code`, `referred_by`, auth/OTP fields | Reuse as EastBound customer/account base. Add contacts via new related table if needed. |
| `travelers` | Saved travelers/passengers under a customer | `App\Models\Traveler` | `customer_id`, `name`, `relation`, `passport_no`, `nationality`, `dob` | Reuse for travelers/passengers. |
| `passports` | Customer passport records | `App\Models\Passport` | `customer_id`, holder/passport details, expiry/status | Reuse. |
| `customer_documents` | Documents held for customers | `App\Models\CustomerDocument` | `customer_id`, `title`, `type`, `file_label`, `uploaded_on`, `status` | Reuse/extend for trip/customer docs, but secure storage needs care. |
| `leads` | CRM lead/enquiry pipeline | `App\Models\Lead` | `assigned_to`, `name`, `phone`, `email`, `interest`, `source`, `value`, `stage`, `owner`, `notes` | Extend existing. Do not create duplicate `leads` unless a separate inbound raw enquiry log is required. |
| `crm_activities` | CRM timeline/communications/follow-ups | `App\Models\CrmActivity` | `customer_id`, `lead_id`, `user_id`, `type`, `subject`, `body`, `activity_date`, `channel` | Extend for follow-ups, communication logs, response tracking. |
| `contact_messages` | Public contact form submissions | `App\Models\ContactMessage` | `name`, `email`, `phone`, `subject`, `message`, `status` | Reuse as inbound message source; can convert/link to leads if needed. |
| `campaigns` | Marketing campaigns | `App\Models\Campaign` | `name`, `channel`, `audience`, `budget`, dates, `status` | Extend for websites/campaign tracking and UTM attribution. |
| `subscribers` | Newsletter subscribers | `App\Models\Subscriber` | email/name/status fields | Reuse only for newsletter audience, not sales leads. |
| `packages` | Tour/package catalogue and sellable tour product | `App\Models\Package` | category, destination, price, child/single pricing, status, itineraries, bookings, schedules, guides/reviews | Reuse as package templates/products. Extend for costing/versioning rather than duplicate. |
| `package_categories` | Package categories | `App\Models\PackageCategory` | `name`, `slug`, `status` | Reuse. |
| `package_itineraries` | Day-by-day package itinerary | `App\Models\PackageItinerary` | `package_id`, day/title/description style fields | Reuse/extend for quotation itinerary templates. |
| `tour_schedules` | Departures/scheduled trips for packages | `App\Models\TourSchedule` | `package_id`, start/end, seats/booked/status | Reuse for group departures; not sufficient as custom DMC trip entity. |
| `bookings` | Main tour package booking/sale | `App\Models\Booking` | `customer_id`, `agent_id`, `package_id`, customer snapshot, `travel_date`, `travelers`, `amount`, discounts, `payment_method`, `status` | Extend or relate to EastBound Trip/Sales Order. It is currently tour package centric. |
| `hotel_bookings` | Hotel stay booking/service sale | `App\Models\HotelBooking` | `booking_no`, `hotel_id`, `hotel_room_id`, `customer_id`, `agent_id`, check-in/out, amount/status/payment | Reuse as trip service/reservation type with linking layer. |
| `transport_bookings` | Transport booking/service sale | `App\Models\TransportBooking` | `booking_id`, `customer_id`, `agent_id`, `driver_id`, `type`, `direction`, `route`, `travel_date`, fare/status | Reuse as trip service/reservation type with linking layer. |
| `flight_bookings` | Flight ticket/request | `App\Models\FlightBooking` | `booking_id`, `customer_id`, `agent_id`, PNR, route, date, fare, settlement status fields | Reuse for flight services. |
| `event_bookings` | Event tour bookings | `App\Models\EventBooking` | `event_tour_id`, `customer_id`, booking no, seats, amount/status/payment | Reuse where event/activity bookings map to EastBound services. |
| `visa_applications` | Visa cases/applications | `App\Models\VisaApplication` | `customer_id`, `package_id`, `visa_service_id`, fees, appointment/expiry/doc status/status | Reuse for visa service operations. |
| `hajj_pilgrims` | Hajj/Umrah registration/passenger operations | `App\Models\HajjPilgrim` | package/customer, passport, group, hotel/flight/seat allocation, paid/due amounts/status | Reuse for Hajj/Umrah operations only. |
| `hotels` | Hotel catalogue/vendor-like product | `App\Models\Hotel` | city/country/category/rooms/price/status, rooms/bookings | Reuse as hotel master data; may need supplier linkage. |
| `hotel_rooms` | Hotel room inventory/rates | `App\Models\HotelRoom` | `hotel_id`, room type, capacity, rate, availability/status | Reuse. |
| `transport_services` | Public transport service catalogue | `App\Models\TransportService` | type/category, route/service metadata, vehicle type/status | Reuse as sellable/service catalogue, not individual reservation. |
| `drivers` | Driver records | `App\Models\Driver` | name/phone/license/status style fields | Reuse for driver assignment. |
| `vehicle_categories` | Vehicle categories | `App\Models\VehicleCategory` | `name`, `sort_order`, `status` | Reuse. |
| `tour_guides` | Guide master records | `App\Models\TourGuide` | name, phone, languages, experience, status, photo/bio/user link | Reuse for guide management. |
| `tour_guide_assignments` | Guide assignments to package/schedule | `App\Models\TourGuideAssignment` | guide, package, schedule, start/end, status, notes | Extend or add generic trip-service guide assignment table. |
| `tour_guide_ratings` | Guide ratings | `App\Models\TourGuideRating` | guide/customer/assignment/rating/comment | Reuse. |
| `suppliers` | Supplier/vendor master | `App\Models\Supplier` | `type`, contact, balance, status | Reuse as EastBound vendor base. Avoid duplicate vendor database. |
| `supplier_contracts` | Supplier agreements/rates | `App\Models\SupplierContract` | supplier, contract no, rate type/value, commission, credit days, dates, document/status | Extend for DMC purchasing contracts/rates. |
| `supplier_transactions` | Supplier ledger/payables | `App\Models\SupplierTransaction` | supplier, contract, debit/credit/balance_after, type, method | Reuse for vendor purchasing/payments. Needs trip/service source links for profitability. |
| `accounts` | Chart of accounts | `App\Models\Account` | code/name/type/balance/opening/system key/cash type | Reuse. |
| `account_transactions` | General ledger/journal rows | `App\Models\AccountTransaction` | account, contra account, source morph, date, amount/type/reference | Reuse carefully. |
| `invoices` | Customer invoices | `App\Models\Invoice` | customer, booking, source morph, amount, paid/refunded, status | Reuse. Add quotation/order context by relation, not rewrite. |
| `receipts` | Customer receipts/payments | `App\Models\Receipt` | invoice, customer, amount, method, reference | Reuse. |
| `refunds` | Refund records | `App\Models\Refund` | invoice, booking, customer, amount/method/reason/processed_by | Reuse. |
| `wallet_transactions` | Customer wallet ledger | `App\Models\WalletTransaction` | customer, type, amount, source morph, balance_after | Reuse. |
| `agent_commissions` | Agent commission records | `App\Models\AgentCommission` | agent, customer, booking/source, amount/rate/status | Reuse for B2B agent commission, probably not internal sales commission without distinction. |
| `agent_wallet_transactions` | Agent wallet ledger | `App\Models\AgentWalletTransaction` | agent, amount/type/source/balance | Reuse for agents. |
| `agent_invoices` | Agent invoices/settlement | `App\Models\AgentInvoice` | agent, invoice no, amount, status, payment settlement fields | Reuse for B2B agents. |
| `agent_withdrawals` | Agent withdrawal requests | `App\Models\AgentWithdrawal` | agent, amount, method, status, processed fields | Reuse. |
| `payment_intents` | Online payment state | `App\Models\PaymentIntent` | gateway, payable morph, customer, amount/currency/status/payload | Reuse for payable online payment. |
| `tasks` | Operational/staff task list | `App\Models\Task` | `assigned_to`, `title`, `project`, `priority`, `due_date`, `status` | Reuse/extend for operations tasks, but needs trip/service links/comments. |
| `support_tickets` | Support tickets | `App\Models\SupportTicket` | customer, assigned user, ticket no, priority/department/status, raised_by | Reuse for customer support; not a substitute for operations tasking. |
| `notifications` | In-app notifications | `App\Models\Notification` | user/notifiable, title/body/category/read | Reuse. |
| `device_tokens` | Push tokens | `App\Models\DeviceToken` | polymorphic notifiable, token, platform | Reuse. |
| `cms_pages` | Public site CMS pages | `App\Models\CmsPage` | slug/status plus marketing/landing/story/contact fields | Reuse for website pages; add campaign attribution separately. |
| `menus` | CMS navigation | `App\Models\Menu` | title/url/position/sort/status and hierarchy fields | Reuse. |
| `content_blocks` | CMS reusable blocks | `App\Models\ContentBlock` | section/status/sort/content fields | Reuse. |
| `blogs` | Blog content | `App\Models\Blog` | title/slug/user/author/status/published/media/body fields | Reuse. |
| `sliders`, `galleries`, `faqs`, `testimonials`, `social_links` | CMS/supporting public content | Various models | Status/sort/media/text fields | Reuse. |
| `currencies` | Currency master | `App\Models\Currency` | name/code/symbol | Reuse, but transactional multi-currency needs additional exchange-rate/currency columns. |
| `trip_plans` | AI/generated customer trip plans | `App\Models\TripPlan` | customer, destination, start date, days, budget/interests, JSON plan | Do not use as confirmed Trip. It is planning/idea data. |
| `loyalty_transactions` | Loyalty points ledger | `App\Models\LoyaltyTransaction` | customer, points, type, source morph, reference | Reuse for B2C loyalty only. |
| `reviews`, `wishlists`, `coupons` | Consumer sales growth features | `Review`, `Wishlist`, `Coupon` | package/booking/customer linkage | Reuse. |
| `settings` | Key/value settings | `App\Models\Backend\Setting` | key/value text | Reuse for EastBound settings. |
| `uploads` | Upload metadata/variants | `App\Models\Upload` | original/type/image variants | Reuse existing upload architecture. |
| `languages`, `flag_icons`, `route_lists` | Localization/routing helpers | Backend models | language/code/icon/direction/route metadata | Reuse. |
| `activity_log` | Spatie activity log | Spatie model | causer/subject/properties/event | Reuse for audit trails. |

## SaaS and Installer Tables

| Table | Purpose | Model | EastBound relevance |
|---|---|---|---|
| `application_installations` | Installer completion/health state | Installer module | Preserve. |
| `tenants`, `domains` | Stancl tenancy central records | `Modules\Saas\Models\Tenant`, domain model from Stancl | Preserve. EastBound should not bypass tenancy mode if enabled. |
| `plans`, `subscriptions`, `payments` | SaaS billing/platform module | `Modules\Saas\Models\Plan`, `Subscription`, `Payment` | Separate from agency accounting. Do not mix with EastBound trip/accounting logic. |

## Semantics Notes

- `bookings` are currently package/tour bookings, not a generic DMC trip container.
- `hotel_bookings`, `transport_bookings`, `flight_bookings`, `event_bookings`, and `hajj_pilgrims` are separate service sale/operation records. Some can link to `bookings`, but they also operate independently.
- `trip_plans` are generated planning artifacts, not operations trips.
- `suppliers` is already a vendor master and should be the base for EastBound vendor management.
- Accounting is event/observer driven. Future EastBound tables that represent billable or payable events should integrate through services/observers rather than direct balance mutation.

## Database Extension Rules for EastBound

- Prefer new additive migrations.
- Do not edit historical FLOW migrations.
- Prefer related tables over adding many nullable columns to core tables.
- Extend existing concepts where semantics match: leads, customers, suppliers, invoices, receipts, tasks, packages, bookings/service bookings.
- Add new modules where FLOW has no equivalent: opportunities, quotations/revisions/items, sales orders/trips, trip service orchestration, vendor purchase orders/service costs, profitability.
