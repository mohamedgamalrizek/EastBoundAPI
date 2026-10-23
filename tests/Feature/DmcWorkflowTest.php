<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Account;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Dmc\Activity;
use App\Models\Dmc\ApprovalPolicy;
use App\Models\Dmc\CampaignSpend;
use App\Models\Dmc\Opportunity;
use App\Models\Dmc\PurchaseRequest;
use App\Models\Dmc\Quotation;
use App\Models\Dmc\Rfq;
use App\Models\Dmc\SalesOrder;
use App\Models\Dmc\Trip;
use App\Models\Dmc\VendorBill;
use App\Models\Dmc\VendorPayment;
use App\Models\Dmc\Website;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use App\Models\User;
use App\Repositories\Dmc\WorkflowRepository;
use App\Services\Accounting\BillingService;
use App\Services\Dmc\ManagementReports;
use App\Services\Dmc\ProcurementWorkflow;
use App\Services\Dmc\SalesWorkflow;
use App\Services\Dmc\TripExpenses;
use App\Services\Dmc\TripOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DmcWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function writer(): User
    {
        return User::factory()->create(['permissions' => ['tour_read', 'tour_create', 'tour_update', 'crm_read', 'crm_create', 'crm_update']]);
    }

    private function manager(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    private function quotation(?User $writer = null): Quotation
    {
        $writer ??= $this->writer();
        $lead = Lead::create(['name' => 'DMC enquiry', 'stage' => 'New', 'source' => 'Website']);
        $opportunity = Opportunity::create([
            'lead_id' => $lead->id, 'customer_id' => Customer::firstOrFail()->id,
            'assigned_to' => $writer->id, 'title' => 'Egypt itinerary',
        ]);

        return app(SalesWorkflow::class)->saveQuotation([
            'opportunity_id' => $opportunity->id, 'title' => 'Egypt itinerary',
            'starts_on' => today()->toDateString(), 'ends_on' => today()->addDays(3)->toDateString(), 'travelers' => 2,
            'items' => [['service_type' => 'domestic_flight', 'description' => 'Cairo to Luxor',
                'service_date' => today()->toDateString(), 'quantity' => 2, 'unit_cost' => 100, 'unit_price' => 150]],
        ], $writer);
    }

    private function approved(): Quotation
    {
        $writer = $this->writer();
        $quote = $this->quotation($writer);
        app(SalesWorkflow::class)->transition($quote, 'submit', $writer);

        return app(SalesWorkflow::class)->transition($quote, 'approve', $this->manager(), ['note' => 'Management reviewed price and itinerary.']);
    }

    private function order(): SalesOrder
    {
        return app(SalesWorkflow::class)->transition($this->approved(), 'accept', $this->manager(), ['customer_confirmation' => 'Signed customer confirmation']);
    }

    private function vendorOrder()
    {
        $order = $this->order();
        $request = PurchaseRequest::where('trip_id', $order->trip->id)->firstOrFail();
        $supplier = Supplier::create(['name' => 'DMC vendor', 'type' => 'Hotel', 'status' => 'active']);
        $rfq = Rfq::create(['purchase_request_id' => $request->id, 'supplier_id' => $supplier->id,
            'valid_until' => today()->addDays(3), 'quoted_amount' => 200]);
        $service = app(ProcurementWorkflow::class);
        $service->transition($rfq, 'send');
        $service->transition($rfq, 'respond');

        return $service->transition($rfq, 'award');
    }

    public function test_unapproved_quotation_cannot_generate_a_sale(): void
    {
        $quote = $this->quotation();
        $before = Booking::count();
        try {
            app(SalesWorkflow::class)->transition($quote, 'accept', $this->manager(), ['customer_confirmation' => 'Email']);
            $this->fail('Unapproved quotation was accepted.');
        } catch (ValidationException) {
            $this->assertSame($before, Booking::count());
            $this->assertSame(0, SalesOrder::count());
        }
    }

    public function test_approved_confirmation_is_idempotent_and_creates_trip_purchasing_and_accounting(): void
    {
        $quote = $this->approved();
        $service = app(SalesWorkflow::class);
        $first = $service->transition($quote, 'accept', $this->manager(), ['customer_confirmation' => 'Email reference']);
        $second = $service->transition($quote, 'accept', $this->manager(), ['customer_confirmation' => 'Email reference']);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, SalesOrder::count());
        $this->assertSame(1, Trip::count());
        $this->assertSame(1, PurchaseRequest::count());
        $this->assertEquals(300, $first->booking->amount);
        $this->assertEquals(200, PurchaseRequest::first()->estimated_cost);
        $this->assertSame($quote->opportunity->lead_id, $first->booking->lead_id);
        $this->assertTrue($first->booking->invoice->journalEntries()->exists());
        $this->assertSame('Won', $quote->opportunity->fresh()->stage);
    }

    public function test_creator_cannot_approve_their_own_quotation(): void
    {
        $writer = $this->manager();
        $quote = $this->quotation($writer);
        app(SalesWorkflow::class)->transition($quote, 'submit', $writer);
        $this->expectException(ValidationException::class);
        app(SalesWorkflow::class)->transition($quote, 'approve', $writer, ['note' => 'Self approval']);
    }

    public function test_approval_policy_is_fail_closed_and_revisions_drop_approval(): void
    {
        $quote = $this->approved();
        $this->assertSame('General Manager', $quote->approval_level);
        $revision = app(SalesWorkflow::class)->transition($quote, 'revise', $this->manager());
        $this->assertSame('Draft', $revision->status);
        $this->assertNull($revision->approved_by);
        $this->assertNull($revision->approved_at);
        $this->assertSame(2, $revision->revision);
        $this->assertSame('Superseded', $quote->fresh()->status);
        $this->assertCount(1, $revision->items);
        ApprovalPolicy::first()->update(['minimum_margin' => 20, 'maximum_markup' => 60, 'team_leader_limit' => 1000]);
        $this->assertSame('Team Leader', app(SalesWorkflow::class)->approvalLevel($revision));
    }

    public function test_vendor_bill_and_payment_post_once_and_never_overpay(): void
    {
        $order = $this->vendorOrder();
        $service = app(ProcurementWorkflow::class);
        $bill = $service->bill(['vendor_order_id' => $order->id, 'reference' => 'VENDOR-100', 'amount' => 120,
            'issued_on' => today()->toDateString(), 'due_on' => today()->addDays(7)->toDateString()]);
        $data = ['vendor_bill_id' => $bill->id, 'amount' => 50, 'paid_on' => today()->toDateString(),
            'method' => 'Bank', 'reference' => 'BANK-100', 'idempotency_key' => (string) Str::uuid()];
        $payment = $service->pay($data);
        $this->assertSame($payment->id, $service->pay($data)->id);
        $this->assertSame(1, VendorPayment::count());
        $this->assertSame(1, VendorBill::count());
        $this->assertEquals(70, $order->supplier->fresh()->balance);
        $this->assertTrue(SupplierTransaction::find($payment->supplier_transaction_id)->journalEntries()->exists());
        try {
            $service->pay(array_merge($data, ['idempotency_key' => (string) Str::uuid(), 'amount' => 71]));
            $this->fail('Overpayment was permitted.');
        } catch (ValidationException) {
            $this->assertSame(1, VendorPayment::count());
            $this->assertEquals(70, $order->supplier->fresh()->balance);
        }
    }

    public function test_alert_generation_does_not_duplicate_tasks(): void
    {
        $trip = $this->order()->trip;
        $service = app(TripOperations::class);
        $this->assertSame(1, $service->generateAlerts());
        $this->assertSame(0, $service->generateAlerts());
        $this->assertSame(1, $trip->alerts()->count());
    }

    public function test_trip_expense_posts_once_and_reaches_profitability(): void
    {
        $trip = $this->order()->trip;
        $data = ['trip_id' => $trip->id, 'title' => 'Operational expense', 'amount' => 25,
            'expense_account_id' => Account::where('type', 'Expense')->firstOrFail()->id,
            'spent_on' => today()->toDateString(), 'method' => 'Cash', 'reference' => 'EXP-1',
            'idempotency_key' => (string) Str::uuid()];
        $service = app(TripExpenses::class);
        $expense = $service->record($data);
        $this->assertSame($expense->id, $service->record($data)->id);
        $this->assertTrue($expense->journalEntries()->exists());
        $report = app(ManagementReports::class)->profitability();
        $this->assertEquals(25, $report['rows'][0][5]);
        $this->assertEquals(275, $report['rows'][0][6]);
    }

    public function test_every_dmc_module_screen_renders_with_real_workflow_records(): void
    {
        $order = $this->vendorOrder();
        $service = app(ProcurementWorkflow::class);
        $bill = $service->bill(['vendor_order_id' => $order->id, 'reference' => 'SCREEN-BILL', 'amount' => 120,
            'issued_on' => today()->toDateString(), 'due_on' => today()->addDays(7)->toDateString()]);
        $service->pay(['vendor_bill_id' => $bill->id, 'amount' => 10, 'paid_on' => today()->toDateString(),
            'method' => 'Cash', 'reference' => 'SCREEN-PAY', 'idempotency_key' => (string) Str::uuid()]);
        $this->actingAs($this->manager());
        foreach (config('dmc-modules') as $module => $definition) {
            $this->get(route('dmc.'.$module.'.index'))->assertOk();
            if ($definition['editable'] || ($definition['create_only'] ?? false)) {
                $this->get(route('dmc.'.$module.'.create'))->assertOk();
            }
            $record = $definition['model']::first();
            if ($record) {
                $this->get(route('dmc.'.$module.'.show', $record->id))->assertOk();
                if (app(WorkflowRepository::class)->canEdit($module, $record)) {
                    $this->get(route('dmc.'.$module.'.edit', $record->id))->assertOk();
                }
            }
        }
        foreach (['receivables', 'profitability', 'cashflow', 'marketing'] as $report) {
            $this->get(route('dmc.reports.'.$report))->assertOk();
            $this->get(route('dmc.reports.'.$report, ['export' => 'csv']))->assertOk();
        }
        $this->get(route('dmc.attribution'))->assertOk();
        $this->get(route('dmc.documents.index'))->assertOk();
        $this->get(route('dmc.trip-documents', $order->trip_id))->assertOk();
        $this->get(route('dmc.alerts'))->assertOk();
    }

    public function test_dmc_intake_requires_complete_canonical_attribution(): void
    {
        $this->postJson('/api/v1/dmc/leads', ['name' => 'Incomplete'])->assertUnprocessable();
        $response = $this->postJson('/api/v1/dmc/leads', [
            'website' => 'english-flow', 'name' => 'Website visitor', 'email' => 'visitor@example.test',
            'language' => 'English', 'country' => 'USA', 'campaign_source' => 'Organic Leads', 'subject' => 'Egypt tour',
            'created_at' => '2000-01-01', 'stage' => 'Won',
        ])->assertCreated();
        $lead = Lead::findOrFail($response->json('data.lead.id'));
        $this->assertSame('English Flow', $lead->website);
        $this->assertNotNull($lead->dmc_website_id);
        $this->assertSame('New', $lead->stage);
        $this->assertTrue($lead->created_at->isToday());
    }

    public function test_department_readers_cannot_create_quotes_or_approve_them(): void
    {
        $reader = User::factory()->create();
        $reader->forceFill(['permissions' => ['dmc_sales_read']])->save();
        $this->actingAs($reader)->get(route('dmc.quotations.index'))->assertOk();
        $this->get(route('dmc.quotations.create'))->assertForbidden();
        $quote = $this->quotation();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->post(route('dmc.quotations.action', $quote->id), ['action' => 'approve', 'note' => 'Invalid approval'])->assertForbidden();
    }

    public function test_accepted_sales_cannot_be_repriced_through_legacy_models(): void
    {
        $order = $this->order();
        $this->expectException(ValidationException::class);
        $order->booking->update(['amount' => 1]);
    }

    public function test_source_delivery_retries_do_not_duplicate_leads(): void
    {
        $payload = ['website' => 'english-flow', 'name' => 'Delivery test', 'email' => 'delivery@example.test',
            'language' => 'English', 'country' => 'USA', 'campaign_source' => 'Organic Leads', 'subject' => 'Tour',
            'external_reference' => 'website-delivery-1'];
        $first = $this->postJson('/api/v1/dmc/leads', $payload)->assertCreated();
        $second = $this->postJson('/api/v1/dmc/leads', $payload)->assertOk();
        $this->assertSame($first->json('data.lead.id'), $second->json('data.lead.id'));
        $this->postJson('/api/v1/dmc/leads', array_merge($payload, ['name' => 'Different person']))->assertUnprocessable();
        $this->assertSame(1, Lead::where('dmc_external_reference', 'website-delivery-1')->count());
    }

    public function test_real_campaign_revenue_spend_and_first_customer_acquisition_are_used(): void
    {
        $writer = $this->writer();
        $quote = $this->quotation($writer);
        $customer = Customer::create(['name' => 'New acquired customer', 'status' => 'active']);
        $quote->opportunity->update(['customer_id' => $customer->id]);
        $site = Website::where('slug', 'english-flow')->firstOrFail();
        $campaign = Campaign::firstOrFail();
        $quote->opportunity->lead->update(['dmc_website_id' => $site->id, 'campaign_id' => $campaign->id]);
        $service = app(SalesWorkflow::class);
        $service->transition($quote, 'submit', $writer);
        $quote = $service->transition($quote, 'approve', $this->manager(), ['note' => 'Approved campaign sale']);
        $order = $service->transition($quote, 'accept', $this->manager(), ['customer_confirmation' => 'Customer email']);
        app(BillingService::class)->recordPayment($order->booking->invoice, 'Bank');
        CampaignSpend::create(['campaign_id' => $campaign->id, 'website_id' => $site->id,
            'spent_on' => today(), 'amount' => 100, 'reference' => 'Google actual spend']);
        $report = app(ManagementReports::class)->marketing();
        $row = $report['rows']->first(fn ($row) => $row[0] === $site->name && $row[1] === '#'.$campaign->id.' '.$campaign->name);
        $this->assertEquals(300, $row[5]);
        $this->assertEquals(300, $row[6]);
        $this->assertEquals(100, $row[7]);
        $this->assertEquals(3, $row[8]);
        $this->assertSame(1, $row[9]);
        $this->assertEquals(100, $row[10]);
    }

    public function test_confirmed_resource_capacity_cannot_be_overallocated(): void
    {
        $trip = $this->order()->trip;
        $item = $trip->items()->firstOrFail();
        $item->quotationItem->update(['service_type' => 'activity']);
        $activity = Activity::create(['name' => 'Limited ticket allocation', 'location' => 'Luxor', 'capacity' => 1]);
        $this->expectException(ValidationException::class);
        app(TripOperations::class)->assign($item, ['activity_id' => $activity->id, 'confirmation' => 'Ticket reference']);
    }

    public function test_customer_quote_pdf_excludes_internal_cost_and_margin_labels(): void
    {
        $quote = $this->approved();
        $this->actingAs($this->manager());
        $html = view('pdf.dmc-commercial', ['module' => 'quotations', 'record' => $quote])->render();
        $this->assertStringNotContainsString('unit_cost', $html);
        $this->assertStringNotContainsString('Margin', $html);
        $this->assertStringNotContainsString('100.00', $html);
        $this->get(route('dmc.document.quotations', $quote->id))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_http_workflow_creates_an_approved_sale_and_operational_trip(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $role = Role::where('slug', 'eastbound-sales')->firstOrFail();
        $writer = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($writer);
        $this->post(route('dmc.opportunities.store'), [
            'title' => 'HTTP itinerary', 'customer_id' => Customer::firstOrFail()->id,
            'assigned_to' => $writer->id, 'expected_value' => 300, 'expected_close' => today()->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $opportunity = Opportunity::latest('id')->firstOrFail();
        $this->post(route('dmc.quotations.store'), [
            'opportunity_id' => $opportunity->id, 'title' => 'HTTP itinerary', 'starts_on' => today()->toDateString(),
            'ends_on' => today()->addDays(2)->toDateString(), 'travelers' => 2, 'total_price' => 1, 'status' => 'Approved',
            'items' => [['service_type' => 'domestic_flight', 'description' => 'Internal flight', 'service_date' => today()->toDateString(), 'quantity' => 2, 'unit_cost' => 100, 'unit_price' => 150]],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $quote = Quotation::latest('id')->firstOrFail();
        $this->assertEquals(300, $quote->total_price);
        $this->assertSame('Draft', $quote->status);
        $this->post(route('dmc.quotations.action', $quote->id), ['action' => 'submit'])->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($this->manager())->post(route('dmc.quotations.action', $quote->id), ['action' => 'approve', 'note' => 'Checked by management'])->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($writer)->post(route('dmc.quotations.action', $quote->id), [
            'action' => 'accept', 'customer_confirmation' => 'Signed acceptance', 'manager_id' => $this->manager()->id,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Accepted', $quote->fresh()->status);
        $this->assertSame(1, Trip::count());
        $this->assertSame($this->manager()->id, Trip::first()->manager_id);
    }

    public function test_procurement_staff_cannot_change_or_read_vendor_bank_details(): void
    {
        $vendor = Supplier::create(['name' => 'Bank protected vendor', 'type' => 'Hotel', 'status' => 'active', 'bank_details' => 'PRIVATE-BANK-DETAILS']);
        $staff = User::factory()->create();
        $staff->forceFill(['permissions' => ['supplier_read', 'supplier_update']])->save();
        $this->actingAs($staff)->get(route('dmc.vendors.show', $vendor->id))->assertOk()->assertDontSee('PRIVATE-BANK-DETAILS');
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->putJson(route('dmc.vendors.update', $vendor->id), ['name' => $vendor->name, 'type' => 'Hotel', 'status' => 'active', 'bank_details' => null])->assertUnprocessable();
        $this->assertSame('PRIVATE-BANK-DETAILS', $vendor->fresh()->bank_details);
        $this->assertNotSame('PRIVATE-BANK-DETAILS', $vendor->fresh()->getRawOriginal('bank_details'));
    }

    public function test_over_budget_procurement_requires_recorded_manager_authorization(): void
    {
        $trip = $this->order()->trip;
        $request = $trip->requests()->firstOrFail();
        $vendor = Supplier::create(['name' => 'Higher cost vendor', 'type' => 'Hotel', 'status' => 'active']);
        $rfq = Rfq::create(['purchase_request_id' => $request->id, 'supplier_id' => $vendor->id, 'quoted_amount' => 250,
            'valid_until' => today()->addDays(3), 'status' => 'Responded']);
        $service = app(ProcurementWorkflow::class);
        try {
            $service->transition($rfq, 'award');
            $this->fail('An unapproved cost overrun was permitted.');
        } catch (ValidationException) {
            $this->assertSame('Requested', $request->fresh()->status);
        }
        $order = $service->transition($rfq, 'award', $this->manager(), 'Approved cost variance');
        $this->assertSame($this->manager()->id, $order->variance_approved_by);
        $this->assertSame('Approved cost variance', $order->variance_reason);
    }

    public function test_trip_lifecycle_requires_confirmed_services_and_purchasing(): void
    {
        $trip = $this->vendorOrder()->trip;
        $service = app(TripOperations::class);
        try {
            $service->transition($trip, 'ready');
            $this->fail('An unconfirmed trip was marked ready.');
        } catch (ValidationException) {
            $this->assertSame('Planning', $trip->fresh()->status);
        }
        $item = $trip->items()->firstOrFail();
        $service->assign($item, ['confirmation' => 'Domestic flight supplier confirmation']);
        $service->confirmItem($item);
        $service->transition($trip, 'ready');
        $service->transition($trip, 'start');
        $this->assertSame('In Progress', $trip->fresh()->status);
        $this->actingAs($this->manager())->get(route('dmc.trip-documents', ['trip' => $trip->id, 'format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_department_dashboard_does_not_depend_on_editable_role_names(): void
    {
        $role = Role::where('slug', 'eastbound-sales')->firstOrFail();
        $role->name = 'Renamed Sales';
        $role->slug = 'renamed-sales';
        $role->save();
        $staff = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($staff)->get(route('dashboard'))->assertOk()->assertSee('Department Dashboard')->assertDontSee('Revenue Overview');
    }
}
