<?php

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Dmc;
use App\Models\Driver;
use App\Models\Hotel;
use App\Models\Lead;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use App\Models\TourGuide;
use App\Models\User;
use Illuminate\Support\Str;

$text = fn (string $label, bool $required = true) => ['label' => $label, 'type' => 'text', 'rules' => [$required ? 'required' : 'nullable', 'string', 'max:255']];
$note = fn (string $label) => ['label' => $label, 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:10000']];
$number = fn (string $label, string $minimum = '0') => ['label' => $label, 'type' => 'number', 'rules' => ['required', 'numeric', 'min:'.$minimum, 'max:999999999']];
$date = fn (string $label) => ['label' => $label, 'type' => 'date', 'rules' => ['required', 'date_format:Y-m-d']];
$choice = fn (string $label, array $options) => ['label' => $label, 'type' => 'select', 'options' => array_combine($options, $options), 'rules' => ['required', 'in:'.implode(',', $options)]];
$relation = fn (string $label, string $model, string $column = 'name', bool $required = true) => [
    'label' => $label, 'type' => 'relation', 'model' => $model, 'column' => $column,
    'rules' => [$required ? 'required' : 'nullable', 'integer', 'exists:'.(str_starts_with($model, 'App\\Models\\Dmc\\') ? 'dmc_' : '').Str::plural(Str::snake(class_basename($model))).',id'],
];
$module = fn (string $title, string $model, string $permission, array $fields = [], bool $editable = true) => [
    'title' => $title, 'model' => $model, 'permission' => $permission, 'fields' => $fields, 'editable' => $editable,
];

return [
    'contacts' => $module('Contacts', Dmc\Contact::class, 'crm', [
        'name' => $text('Name'), 'customer_id' => $relation('Customer', Customer::class, required: false),
        'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
        'phone' => $text('Phone', false), 'position' => $text('Position', false), 'notes' => $note('Notes'),
    ]),
    'vendors' => $module('Vendor Profiles & Payment Terms', Supplier::class, 'supplier', [
        'name' => $text('Vendor name'), 'type' => $choice('Type', ['Hotel', 'Transport', 'Guide', 'Activity', 'Airline', 'Other']),
        'contact_person' => $text('Contact person', false), 'phone' => $text('Phone', false),
        'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
        'payment_terms' => $text('Payment terms', false),
        'currency' => ['label' => 'Vendor currency (ISO code; informational)', 'type' => 'text', 'rules' => ['nullable', 'regex:/^[A-Z]{3}$/']],
        'bank_details' => $note('Bank details (encrypted)') + ['permission' => 'accounting_update'],
        'rating' => ['label' => 'Rating (1–5)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:5']],
        'status' => $choice('Status', ['active', 'inactive']),
    ]),
    'opportunities' => $module('Opportunities / Deals', Dmc\Opportunity::class, 'crm', [
        'title' => $text('Opportunity'), 'lead_id' => $relation('Originating lead', Lead::class, required: false),
        'customer_id' => $relation('Customer', Customer::class),
        'assigned_to' => $relation('Sales owner', User::class),
        'expected_value' => $number('Expected value'), 'expected_close' => $date('Expected close date'),
        'notes' => $note('Notes'),
    ]),
    'quotations' => $module('Quotations', Dmc\Quotation::class, 'dmc_sales', [
        'opportunity_id' => $relation('Opportunity', Dmc\Opportunity::class, 'title'), 'title' => $text('Quotation title'),
        'starts_on' => $date('Trip starts'),
        'ends_on' => ['label' => 'Trip ends', 'type' => 'date', 'rules' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on']],
        'travelers' => ['label' => 'Travelers', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:10000']],
        'notes' => $note('Itinerary / terms'),
    ]),
    'sales-orders' => $module('Sales Orders', Dmc\SalesOrder::class, 'dmc_sales', editable: false),
    'trips' => $module('Trips / Operational Projects', Dmc\Trip::class, 'dmc_operations', [
        'manager_id' => $relation('Operations manager', User::class),
    ], false) + ['update_only' => true],
    'activities' => $module('Activities & Tickets', Dmc\Activity::class, 'dmc_operations', [
        'name' => $text('Activity / ticket'), 'location' => $text('Location'),
        'supplier_id' => $relation('Vendor', Supplier::class, required: false),
        'capacity' => ['label' => 'Daily capacity', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:100000']],
        'cost' => $number('Unit cost'), 'price' => $number('Unit selling price'),
        'active' => ['label' => 'Active', 'type' => 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'rules' => ['required', 'boolean']],
    ]),
    'trip-items' => $module('Daily Operations & Resource Assignments', Dmc\TripItem::class, 'dmc_operations', [
        'hotel_id' => $relation('Hotel', Hotel::class, required: false),
        'driver_id' => $relation('Driver / vehicle', Driver::class, required: false),
        'guide_id' => $relation('Guide', TourGuide::class, required: false),
        'activity_id' => $relation('Activity / ticket', Dmc\Activity::class, required: false),
        'confirmation' => $text('Vendor confirmation / booking reference', false), 'notes' => $note('Operational notes'),
    ], false) + ['update_only' => true],
    'purchase-requests' => $module('Purchase Requests', Dmc\PurchaseRequest::class, 'dmc_procurement', editable: false),
    'rfqs' => $module('Requests for Quotation', Dmc\Rfq::class, 'dmc_procurement', [
        'purchase_request_id' => $relation('Purchase request', Dmc\PurchaseRequest::class, 'description'),
        'supplier_id' => $relation('Vendor', Supplier::class), 'valid_until' => $date('Valid until'),
        'quoted_amount' => ['label' => 'Vendor quoted amount (record after response)', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'gt:0', 'max:999999999']],
        'notes' => $note('Terms / notes'),
    ]),
    'vendor-orders' => $module('Vendor Orders', Dmc\VendorOrder::class, 'dmc_procurement', editable: false),
    'vendor-bills' => $module('Vendor Bills', Dmc\VendorBill::class, 'accounting', [
        'vendor_order_id' => $relation('Vendor order', Dmc\VendorOrder::class, 'id'),
        'reference' => $text('Vendor invoice reference'), 'amount' => $number('Bill amount', '0.01'),
        'issued_on' => $date('Issue date'),
        'due_on' => ['label' => 'Payment due', 'type' => 'date', 'rules' => ['required', 'date_format:Y-m-d', 'after_or_equal:issued_on']],
    ], false) + ['create_only' => true],
    'vendor-payments' => $module('Vendor Payments', Dmc\VendorPayment::class, 'accounting', [
        'vendor_bill_id' => $relation('Vendor bill', Dmc\VendorBill::class, 'reference'),
        'amount' => $number('Payment amount', '0.01'), 'paid_on' => $date('Payment date'),
        'method' => $choice('Payment method', SupplierTransaction::METHODS), 'reference' => $text('Bank / payment reference'),
        'idempotency_key' => ['label' => 'Payment identifier', 'type' => 'hidden', 'rules' => ['required', 'uuid']],
    ], false) + ['create_only' => true],
    'websites' => $module('Company Websites Management', Dmc\Website::class, 'crm', [
        'name' => $text('Website name'), 'slug' => ['label' => 'Source identifier', 'type' => 'text', 'rules' => ['required', 'alpha_dash', 'max:255']],
        'domain' => ['label' => 'Domain (hostname only)', 'type' => 'text', 'rules' => ['nullable', 'regex:/^(?!-)[a-z0-9-]+(?:\.[a-z0-9-]+)+$/i', 'max:255']],
        'language' => ['label' => 'Default language', 'type' => 'select', 'options' => array_combine(Lead::LANGUAGES, Lead::LANGUAGES), 'rules' => ['nullable', 'in:'.implode(',', Lead::LANGUAGES)]],
        'active' => ['label' => 'Active', 'type' => 'select', 'options' => [1 => 'Yes', 0 => 'No'], 'rules' => ['required', 'boolean']],
    ]) + ['write_permission' => 'general_settings_update'],
    'trip-expenses' => $module('Trip Expenses', Dmc\TripExpense::class, 'accounting', [
        'trip_id' => $relation('Trip', Dmc\Trip::class, 'title'),
        'expense_account_id' => $relation('Expense account', Account::class),
        'title' => $text('Expense description'), 'amount' => $number('Amount', '0.01'), 'spent_on' => $date('Expense date'),
        'method' => $choice('Payment method', ['Cash', 'Bank']), 'reference' => $text('Payment reference'),
        'idempotency_key' => ['label' => 'Expense identifier', 'type' => 'hidden', 'rules' => ['required', 'uuid']],
    ], false) + ['create_only' => true],
    'campaign-spends' => $module('Campaign Spend', Dmc\CampaignSpend::class, 'campaign', [
        'campaign_id' => $relation('Campaign', Campaign::class),
        'website_id' => $relation('Website', Dmc\Website::class), 'spent_on' => $date('Spend date'),
        'amount' => $number('Actual spend', '0.01'), 'reference' => $text('Spend reference'),
    ]),
    'approval-policies' => $module('Quotation Approval Policy', Dmc\ApprovalPolicy::class, 'general_settings', [
        'minimum_margin' => ['label' => 'Minimum margin % for Team Leader approval', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0', 'max:99.99']],
        'maximum_markup' => ['label' => 'Maximum markup % for Team Leader approval', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0', 'max:999999']],
        'team_leader_limit' => ['label' => 'Maximum selling total for Team Leader approval', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0', 'max:999999999']],
    ], false) + ['update_only' => true],
];
