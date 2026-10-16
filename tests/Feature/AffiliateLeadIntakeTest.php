<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateLeadIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_affiliate_website_can_submit_tour_leads(): void
    {
        $response = $this->postJson('/api/v1/leads', [[
            'created_at'   => '2026-09-21 09:44:13',
            'full_name'    => 'Martina Mohsen',
            'email'        => 'martina.m.nmr@gmail.com',
            'phone'        => '20 1225640819',
            'website'      => 'English',
            'destinations' => 'Egypt Tours',
            'from_date'    => '2026-09-23',
            'to_date'      => '2026-10-01',
            'duration'     => 2,
            'adults'       => 1,
            'children'     => 0,
            'budget'       => 'high',
            'value'        => 2500,
            'comments'     => 'نتمناتماتا',
        ]]);

        $response->assertCreated()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.leads.0.website', 'English');

        $lead = Lead::firstOrFail();

        $this->assertSame('Martina Mohsen', $lead->name);
        $this->assertSame('Egypt Tours', $lead->interest);
        $this->assertSame('Website', $lead->source);
        $this->assertSame('English', $lead->website);
        $this->assertEqualsWithDelta(2500, (float) $lead->value, 0.01);
        $this->assertSame('Egypt Tours', $lead->destinations);
        $this->assertSame('2026-09-23', $lead->from_date->format('Y-m-d'));
        $this->assertSame('2026-10-01', $lead->to_date->format('Y-m-d'));
        $this->assertSame(2, $lead->duration);
        $this->assertSame(1, $lead->adults);
        $this->assertSame(0, $lead->children);
        $this->assertSame('high', $lead->budget);
        $this->assertSame('نتمناتماتا', $lead->comments);
        $this->assertStringContainsString('From Date: 2026-09-23', $lead->notes);
        $this->assertStringContainsString('Adults: 1', $lead->notes);
    }

    public function test_contact_endpoint_keeps_subject_and_website_on_the_lead(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Ahmed Hassan',
            'email'   => 'ahmed@example.com',
            'phone'   => '+201000000000',
            'website' => 'Italy',
            'subject' => 'Visa services',
            'message' => 'I need help with a tourist visa.',
        ]);

        $response->assertCreated();

        $this->assertSame('Italy', ContactMessage::firstOrFail()->website);

        $lead = Lead::firstOrFail();

        $this->assertSame('Visa services', $lead->interest);
        $this->assertSame('Italy', $lead->website);
        $this->assertStringContainsString('Contact message #', $lead->notes);
    }
}
