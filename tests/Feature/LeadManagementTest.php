<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'name' => 'Ahmed Hassan',
            'email' => 'ahmed@example.com',
            'phone' => '+201000000000',
            'interest' => 'Cairo, Luxor',
            'source' => 'Website',
            'website' => 'Egypt Tours',
            'language' => 'English',
            'country' => 'Egypt',
            'campaign_source' => 'Google Ads',
            'subject' => 'Tour packages',
            'destinations' => 'Cairo, Luxor',
            'from_date' => '2026-11-01',
            'to_date' => '2026-11-07',
            'duration' => 7,
            'adults' => 2,
            'children' => 0,
            'budget' => 'high',
            'value' => 2500.50,
            'stage' => 'New',
            'comments' => 'عايز برنامج شامل الإقامة',
            'notes' => "Internal note\nFollow up tomorrow",
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->actingAs(User::where('email', 'superadmin@bugbuild.com')->firstOrFail());
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_create_and_edit_forms_support_all_intake_fields(): void
    {
        $this->get(route('crm.leads.create'))->assertOk()
            ->assertSee('name="website"', false)
            ->assertSee('name="campaign_source"', false)
            ->assertSee('name="destinations"', false)
            ->assertSee('name="comments"', false);

        $lead = Lead::create($this->payload());

        $this->get(route('crm.leads.edit', $lead->id))->assertOk()
            ->assertSee('value="Egypt Tours"', false)
            ->assertSee('value="2026-11-01"', false)
            ->assertSee('value="0"', false)
            ->assertSee('عايز برنامج شامل الإقامة');
    }

    public function test_staff_can_create_and_update_trip_and_attribution_details(): void
    {
        $payload = $this->payload();
        $payload['created_at'] = '2026-10-16T10:00:00';

        $this->post(route('crm.leads.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('crm.leads'));

        $lead = Lead::where('email', $payload['email'])->firstOrFail();
        $this->assertSame('2026-10-16 10:00:00', $lead->created_at->format('Y-m-d H:i:s'));
        $this->assertSame(0, $lead->children);
        $this->assertSame('high', $lead->budget);
        $this->assertSame($payload['comments'], $lead->comments);

        $payload['language'] = 'Italian';
        $payload['campaign_source'] = 'Referral';
        $payload['children'] = 1;
        $payload['comments'] = 'Updated request';
        $payload['budget'] = null;

        $this->put(route('crm.leads.update', $lead->id), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('crm.leads'));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'language' => 'Italian',
            'campaign_source' => 'Referral',
            'children' => 1,
            'budget' => null,
            'comments' => 'Updated request',
            'from_date' => '2026-11-01',
        ]);
    }

    public function test_list_and_details_show_intake_data_and_escape_comments(): void
    {
        $lead = Lead::create(array_merge($this->payload(), ['comments' => '<script>alert(1)</script>']));

        $this->get(route('crm.leads'))->assertOk()
            ->assertSee('Egypt Tours')->assertSee('Google Ads')
            ->assertSee('2026-11-01')->assertSee('Children: 0')->assertSee('2,500.50');

        $this->get(route('crm.leads.show', $lead->id))->assertOk()
            ->assertSee('Egypt Tours')->assertSee('Google Ads')
            ->assertSee('2026-11-01')->assertSee('high')
            ->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_invalid_trip_details_are_rejected_on_create_and_update(): void
    {
        $lead = Lead::create($this->payload());
        $payload = array_merge($this->payload(), [
            'to_date' => '2026-10-01',
            'adults' => -1,
            'language' => 'Invalid',
            'campaign_source' => 'Invalid',
        ]);

        $this->post(route('crm.leads.store'), $payload)
            ->assertSessionHasErrors(['to_date', 'adults', 'language', 'campaign_source']);
        $this->put(route('crm.leads.update', $lead->id), $payload)
            ->assertSessionHasErrors(['to_date', 'adults', 'language', 'campaign_source']);
        $this->assertSame(2, $lead->fresh()->adults);
    }
}
