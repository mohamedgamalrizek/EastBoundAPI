<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises store/update/delete on the CMS modules that feed the public
 * website, so a repository <-> FormRequest mismatch fails here rather than
 * silently dropping a column in production.
 */
class AdminCmsWriteTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml)
    // and seeds the full demo dataset the public site renders.
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
    }

    public function test_visa_service_round_trip(): void
    {
        // `fee` is derived (govt_fee + service_fee) since the fee split — the
        // form posts the two parts, never the total.
        $payload = [
            'country' => 'Testland', 'flag' => '🏳️', 'visa_type' => 'Tourist',
            'processing_time' => '7 days', 'govt_fee' => 4000, 'service_fee' => 321,
            'requirements' => "Passport\nPhoto",
            'is_featured' => 1, 'sort_order' => 99, 'status' => 'active',
        ];

        $this->actingAs($this->admin())->post(route('cms.visa-service.store'), $payload)
            ->assertRedirect(route('cms.visa-service.index'));

        $row = \App\Models\VisaService::where('country', 'Testland')->firstOrFail();
        $this->assertSame('7 days', $row->processing_time);
        $this->assertSame('4321.00', $row->fee);
        $this->assertTrue($row->is_featured);
        $this->assertSame(['Passport', 'Photo'], $row->requirementList());

        $this->actingAs($this->admin())
            ->put(route('cms.visa-service.update'), ['id' => $row->id] + $payload + ['govt_fee' => 80, 'service_fee' => 20])
            ->assertRedirect(route('cms.visa-service.index'));

        $this->actingAs($this->admin())->delete(route('cms.visa-service.delete', $row->id))->assertOk();
        $this->assertNull(\App\Models\VisaService::find($row->id));
    }

    public function test_flight_route_round_trip(): void
    {
        $this->actingAs($this->admin())->post(route('cms.flight-route.store'), [
            'origin' => 'Testville', 'origin_code' => 'TST', 'destination' => 'Elsewhere',
            'destination_code' => 'ELS', 'airline' => 'Test Air', 'fare' => 1234,
            'trip_type' => 'One-way', 'is_featured' => 0, 'sort_order' => 99, 'status' => 'active',
        ])->assertRedirect(route('cms.flight-route.index'));

        $row = \App\Models\FlightRoute::where('origin', 'Testville')->firstOrFail();
        $this->assertSame('Test Air', $row->airline);
        $this->assertSame('Testville → Elsewhere', $row->label);
    }

    public function test_transport_service_round_trip(): void
    {
        $this->actingAs($this->admin())->post(route('cms.transport-service.store'), [
            'title' => 'Test Shuttle', 'icon' => 'fa-van-shuttle', 'description' => 'Testing.',
            'price_from' => 500, 'price_unit' => '/trip', 'booking_type' => 'airport-pickup',
            'sort_order' => 99, 'status' => 'active',
        ])->assertRedirect(route('cms.transport-service.index'));

        $row = \App\Models\TransportService::where('title', 'Test Shuttle')->firstOrFail();
        $this->assertSame('airport-pickup', $row->booking_type);
    }

    /** booking_type must resolve to a real /book/{type} page. */
    public function test_transport_service_rejects_unknown_booking_type(): void
    {
        $this->actingAs($this->admin())->post(route('cms.transport-service.store'), [
            'title' => 'Bad Service', 'booking_type' => 'not-a-real-type', 'status' => 'active',
        ])->assertSessionHasErrors('booking_type');
    }

    public function test_job_opening_round_trip(): void
    {
        $this->actingAs($this->admin())->post(route('cms.job-opening.store'), [
            'title' => 'Test Role', 'department' => 'QA', 'location' => 'Remote',
            'employment_type' => 'Contract', 'description' => 'Testing.',
            'closing_date' => now()->addMonth()->toDateString(), 'sort_order' => 99, 'status' => 'active',
        ])->assertRedirect(route('cms.job-opening.index'));

        $row = \App\Models\JobOpening::where('title', 'Test Role')->firstOrFail();
        $this->assertSame('Contract', $row->employment_type);
    }

    /** Past-dated vacancies must drop off the public careers page. */
    public function test_expired_job_opening_is_hidden_from_public_page(): void
    {
        $expired = \App\Models\JobOpening::create([
            'title' => 'Expired Role', 'employment_type' => 'Full-time',
            'closing_date' => now()->subDay(), 'status' => 'active',
        ]);

        $this->get(route('front.career'))->assertOk()->assertDontSee('Expired Role');
        $this->assertTrue(\App\Models\JobOpening::open()->where('id', $expired->id)->doesntExist());
    }

    /** The new blog columns must survive a save through the admin form. */
    public function test_blog_new_columns_persist(): void
    {
        $this->actingAs($this->admin())->post(route('cms.blog.store'), [
            'title' => 'Test Post', 'slug' => 'test-post-write-check', 'author' => 'QA',
            // image_url is the "paste a link" half of the image field.
            'category' => 'Tips', 'image_url' => 'https://example.com/a.jpg',
            'excerpt' => 'Short summary.', 'body' => "## Heading\n\nBody text.",
            'read_minutes' => 9, 'status' => 'published', 'published_at' => now()->toDateString(),
        ])->assertRedirect(route('cms.blog.index'));

        $post = \App\Models\Blog::where('slug', 'test-post-write-check')->firstOrFail();
        $this->assertSame('Tips', $post->category);
        $this->assertSame(9, $post->read_minutes);
        $this->assertStringContainsString('Body text.', $post->body);

        // …and render on the public detail page as Markdown.
        $this->get(route('front.blog.show', $post->slug))
            ->assertOk()
            ->assertSee('<h2>Heading</h2>', false);
    }

    /* ===================== Image field ===================== */

    /** Uploading a file stores it under public/uploads and wins over the URL box. */
    public function test_image_upload_is_stored_and_beats_the_url_field(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('hero.jpg', 800, 600);

        $this->actingAs($this->admin())->post(route('cms.gallery.store'), [
            'title'     => 'Upload Test Photo',
            'image'     => $file,
            'image_url' => 'https://example.com/ignored.jpg',
            'status'    => 'active',
        ])->assertRedirect(route('cms.gallery.index'));

        $row = \App\Models\Gallery::where('title', 'Upload Test Photo')->firstOrFail();

        $this->assertStringStartsWith('uploads/gallery/', $row->image);
        $this->assertFileExists(public_path($row->image));

        // media_url() turns the stored path into a usable src.
        $this->assertStringContainsString($row->image, media_url($row->image));

        @unlink(public_path($row->image));
    }

    /** With no file, the pasted URL is kept; clearing the box removes the image. */
    public function test_image_url_is_kept_and_can_be_cleared(): void
    {
        $this->actingAs($this->admin())->post(route('cms.gallery.store'), [
            'title' => 'URL Test Photo', 'image_url' => 'https://example.com/a.jpg', 'status' => 'active',
        ])->assertRedirect(route('cms.gallery.index'));

        $row = \App\Models\Gallery::where('title', 'URL Test Photo')->firstOrFail();
        $this->assertSame('https://example.com/a.jpg', $row->image);

        $this->actingAs($this->admin())->put(route('cms.gallery.update'), [
            'id' => $row->id, 'title' => 'URL Test Photo', 'image_url' => '', 'status' => 'active',
        ])->assertRedirect(route('cms.gallery.index'));

        $this->assertNull($row->fresh()->image);
    }

    /** Editing without touching the image must not wipe it. */
    public function test_editing_without_a_file_keeps_the_existing_image(): void
    {
        $row = \App\Models\Gallery::create([
            'title' => 'Keeper', 'image' => 'https://example.com/keep.jpg', 'status' => 'active',
        ]);

        $this->actingAs($this->admin())->put(route('cms.gallery.update'), [
            'id' => $row->id, 'title' => 'Keeper Renamed',
            'image_url' => $row->image, 'status' => 'active',
        ])->assertRedirect(route('cms.gallery.index'));

        $this->assertSame('https://example.com/keep.jpg', $row->fresh()->image);
    }

    /* ===================== Package itinerary ===================== */

    /** A package's day-by-day plan round-trips and renders on the public page. */
    public function test_package_itinerary_round_trip(): void
    {
        $package = \App\Models\Package::where('status', 'active')->firstOrFail();

        $this->actingAs($this->admin())->put(route('package.update', $package->id), [
            'title'           => $package->title,
            'destination'     => $package->destination,
            'category_id'     => $package->category_id ?: \App\Models\PackageCategory::value('id'),
            'price'           => $package->price,
            'duration_days'   => $package->duration_days,
            'duration_nights' => $package->duration_nights,
            'status'          => 'active',
            'image_url'       => $package->image,
            'itinerary'       => [
                ['day_number' => 1, 'title' => 'Touchdown day',  'description' => 'Arrive and rest.'],
                // Blank rows are template rows and must be ignored.
                ['day_number' => 2, 'title' => '',               'description' => 'no title, skip me'],
                ['day_number' => 2, 'title' => 'Mountain day',   'description' => 'Head for the hills.'],
            ],
        ])->assertRedirect(route('package.index'));

        $days = $package->fresh()->itineraries;
        $this->assertCount(2, $days);
        $this->assertSame(['Touchdown day', 'Mountain day'], $days->pluck('title')->all());

        $this->get(route('front.package', $package->id))
            ->assertOk()
            ->assertSee('Touchdown day')
            ->assertSee('Head for the hills.')
            ->assertDontSee('no title, skip me');
    }

    /** A package with no itinerary hides the section rather than inventing days. */
    public function test_package_without_itinerary_hides_the_section(): void
    {
        $package = \App\Models\Package::where('status', 'active')->firstOrFail();
        $package->itineraries()->delete();

        $this->get(route('front.package', $package->id))
            ->assertOk()
            ->assertDontSee('Day-by-day itinerary');
    }

    /* ===================== Navigation ===================== */

    /**
     * A child lives in its parent's region: submitting a mismatched region is
     * rejected outright (ValidMenuParent), and a matching one is accepted.
     */
    public function test_menu_child_region_must_match_parent(): void
    {
        $parent = \App\Models\Menu::create([
            'title' => 'Parent Group', 'position' => \App\Models\Menu::POSITION_FOOTER, 'status' => 'active',
        ]);

        // Mismatched region — must be refused with a clear validation error.
        $this->actingAs($this->admin())
            ->from(route('cms.menu.index'))
            ->post(route('cms.menu.store'), [
                'parent_id' => $parent->id,
                'title'     => 'Child Link',
                'url'       => '/faq',
                'position'  => \App\Models\Menu::POSITION_HEADER,
                'sort_order' => 1,
                'status'    => 'active',
            ])->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('menus', ['title' => 'Child Link']);

        // Matching region — accepted, child lands under the parent.
        $this->actingAs($this->admin())->post(route('cms.menu.store'), [
            'parent_id' => $parent->id,
            'title'     => 'Child Link',
            'url'       => '/faq',
            'position'  => \App\Models\Menu::POSITION_FOOTER,
            'sort_order' => 1,
            'status'    => 'active',
        ])->assertRedirect(route('cms.menu.index'));

        $child = \App\Models\Menu::where('title', 'Child Link')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame(\App\Models\Menu::POSITION_FOOTER, $child->position);
    }

    /** Deleting a group that still holds links must be refused. */
    public function test_menu_with_children_cannot_be_deleted(): void
    {
        $parent = \App\Models\Menu::create([
            'title' => 'Group With Kids', 'position' => \App\Models\Menu::POSITION_HEADER, 'status' => 'active',
        ]);
        \App\Models\Menu::create([
            'parent_id' => $parent->id, 'title' => 'A Child',
            'position' => \App\Models\Menu::POSITION_HEADER, 'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin())->delete(route('cms.menu.delete', $parent->id));

        $this->assertFalse($response->json('status'));
        $this->assertNotNull(\App\Models\Menu::find($parent->id));
    }

    /** Admin-authored markup must not reach the public page as live HTML. */
    public function test_cms_page_body_strips_raw_html(): void
    {
        \App\Models\CmsPage::updateOrCreate(
            ['slug' => 'privacy-policy'],
            ['title' => 'Privacy Policy', 'status' => 'published', 'body' => "## Safe\n\n<script>alert(1)</script>"]
        );

        $this->get(route('front.privacy'))
            ->assertOk()
            ->assertSee('<h2>Safe</h2>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
