<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Visa cases opened by the applicant, and the tracking screen that works them.
 *
 * The two defects behind these tests: the website form only wrote a CRM lead
 * (so the module never saw the application), and the app's endpoint wrote a
 * status outside the module's vocabulary (so its cases could never be
 * advanced).
 */
class VisaCaseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    public function test_the_website_form_opens_a_real_case(): void
    {
        $this->post(route('front.book.request', 'visa'), [
            'country'     => 'Canada',
            'visa_type'   => 'Student',
            'nationality' => 'Bangladeshi',
            'travel_date' => now()->addMonths(3)->toDateString(),
            'name'        => 'Rumana Haque',
            'phone'       => '01911223344',
            'email'       => 'rumana.website@example.com',
            'details'     => 'Two dependants travelling with me.',
        ])->assertRedirect(route('front.book', 'visa'))->assertSessionHas('success');

        $application = VisaApplication::where('applicant_name', 'Rumana Haque')->first();

        $this->assertNotNull($application, 'The form must open a case, not only a lead.');
        $this->assertSame('Canada', $application->country);
        $this->assertSame('Student', $application->visa_type);

        // In the module's own vocabulary, so every tile and the transition
        // table can work with it.
        $this->assertSame('Processing', $application->status);
        $this->assertSame('Pending', $application->documents_status);

        // Not filed with an embassy yet.
        $this->assertNull($application->applied_date);

        // The answers with no column of their own stay with the case.
        $this->assertStringContainsString('Bangladeshi', $application->appointment_notes);

        // A new caller becomes a customer the case is attached to; an email
        // the agency already knows would have matched that record instead.
        $this->assertNotNull(Customer::where('phone', '01911223344')->first());
        $this->assertSame($application->customer_id, Customer::where('phone', '01911223344')->value('id'));
        $this->assertNotNull(Lead::where('name', 'Rumana Haque')->first());
    }

    public function test_resubmitting_does_not_open_a_second_case(): void
    {
        $payload = [
            'country'   => 'Malaysia',
            'visa_type' => 'Tourist',
            'name'      => 'Repeat Clicker',
            'phone'     => '01911223355',
        ];

        $this->post(route('front.book.request', 'visa'), $payload)->assertRedirect();
        $this->post(route('front.book.request', 'visa'), $payload)->assertRedirect();

        $this->assertSame(1, VisaApplication::where('applicant_name', 'Repeat Clicker')->count());
    }

    public function test_an_app_created_case_can_be_advanced_by_the_desk(): void
    {
        $customer = Customer::firstOrFail();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/visa', ['country' => 'Japan', 'visa_type' => 'Business'])
            ->assertCreated();

        $application = VisaApplication::where('country', 'Japan')->firstOrFail();
        $this->assertSame('Processing', $application->status);

        // This is what used to be impossible: `pending` had no transition rule,
        // so the desk could not move the case anywhere.
        $this->actingAs($this->admin())
            ->put(route('visa.advance', $application->id), [
                'status' => 'In Review', 'documents_status' => 'Verified',
            ])->assertRedirect();

        $application->refresh();
        $this->assertSame('In Review', $application->status);
        $this->assertNotNull($application->applied_date, 'Reaching the embassy stamps the filing date.');
    }

    public function test_progress_follows_the_status_then_the_documents(): void
    {
        // On the desk: the paperwork is what moves the bar.
        $this->assertSame(15, VisaApplication::progressFor('Processing', 'Pending'));
        $this->assertSame(40, VisaApplication::progressFor('Processing', 'Submitted'));
        $this->assertSame(60, VisaApplication::progressFor('Processing', 'Verified'));

        // With the embassy, and decided.
        $this->assertSame(75, VisaApplication::progressFor('In Review', 'Pending'));
        $this->assertSame(100, VisaApplication::progressFor('Approved', 'Pending'));
        $this->assertSame(100, VisaApplication::progressFor('Rejected', 'Verified'));
    }

    public function test_the_applications_page_counts_in_review(): void
    {
        $inReview = VisaApplication::where('status', 'In Review')->count();
        $this->assertGreaterThan(0, $inReview, 'The seed should leave cases in review.');

        // The tile row used to render blank where this count belongs.
        $this->actingAs($this->admin())
            ->get(route('visa.applications'))
            ->assertOk()
            ->assertSee('In Review');
    }

    public function test_the_tracking_screen_renders_with_the_live_preview_hooks(): void
    {
        $this->actingAs($this->admin())
            ->get(route('visa.tracking'))
            ->assertOk()
            ->assertSee('js-visa-status', false)
            ->assertSee('js-visa-bar', false);
    }
}
