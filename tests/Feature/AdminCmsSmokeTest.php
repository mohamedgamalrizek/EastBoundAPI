<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every admin CMS screen touched by the "make the website dynamic"
 * work, so a blade/variable mistake fails loudly instead of at runtime.
 */
class AdminCmsSmokeTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml).
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_cms_screens_render(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $routes = [
            // New public-website catalogues
            'cms.visa-service.index', 'cms.visa-service.create',
            'cms.flight-route.index', 'cms.flight-route.create',
            'cms.transport-service.index', 'cms.transport-service.create',
            'cms.job-opening.index', 'cms.job-opening.create',
            'cms.content-block.index', 'cms.content-block.create',
            // Existing modules whose forms gained new fields
            'cms.index', 'cms.create',
            'cms.blog.index', 'cms.blog.create',
            'cms.gallery.index', 'cms.gallery.create',
            'cms.slider.index', 'cms.slider.create',
            'cms.testimonial.index', 'cms.testimonial.create',
            'cms.faq.index', 'cms.faq.create',
            'hotel.index', 'hotel.create',
            'package.index', 'package.create',
            'settings.general.index',
        ];

        foreach ($routes as $name) {
            $response = $this->actingAs($admin)->get(route($name));
            $this->assertSame(200, $response->status(), "GET {$name} returned {$response->status()}");
        }
    }

    public function test_flight_booking_form_renders_route_picker(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        // Create: the From/To selects are present and fed from published routes.
        $create = $this->actingAs($admin)->get(route('flight.create'));
        $create->assertOk();
        $create->assertSee('id="route_from"', false);
        $create->assertSee('id="route_to"', false);
        $create->assertSee('Dhaka (DAC)', false);

        // Edit: an existing seeded route stored as "DAC→DXB" splits back into
        // the two pickers (matched by airport code) and is preselected.
        $id = \App\Models\FlightBooking::value('id');
        $this->assertNotNull($id, 'no seed flight booking');

        $edit = $this->actingAs($admin)->get(route('flight.edit', $id));
        $edit->assertOk();
        $edit->assertSee('selected', false);
    }

    public function test_admin_edit_screens_render(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $edits = [
            'cms.visa-service.edit'      => \App\Models\VisaService::value('id'),
            'cms.flight-route.edit'      => \App\Models\FlightRoute::value('id'),
            'cms.transport-service.edit' => \App\Models\TransportService::value('id'),
            'cms.job-opening.edit'       => \App\Models\JobOpening::value('id'),
            'cms.content-block.edit'     => \App\Models\ContentBlock::value('id'),
            'cms.blog.edit'              => \App\Models\Blog::value('id'),
            'cms.gallery.edit'           => \App\Models\Gallery::value('id'),
            'cms.slider.edit'            => \App\Models\Slider::value('id'),
            'cms.testimonial.edit'       => \App\Models\Testimonial::value('id'),
            'cms.edit'                   => \App\Models\CmsPage::value('id'),
            'hotel.edit'                 => \App\Models\Hotel::value('id'),
            'package.edit'               => \App\Models\Package::value('id'),
        ];

        foreach ($edits as $name => $id) {
            $this->assertNotNull($id, "no seed row for {$name}");
            $response = $this->actingAs($admin)->get(route($name, $id));
            $this->assertSame(200, $response->status(), "GET {$name} returned {$response->status()}");
        }
    }
}
