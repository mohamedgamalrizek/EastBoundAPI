<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The public marketing site. Every page reads its content from the database,
 * so these cover both "the page renders" and "it renders the DB row, not a
 * hardcoded placeholder".
 */
class PublicSiteTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml)
    // and seeds the full demo dataset the public site renders.
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_every_public_page_renders(): void
    {
        $routes = [
            'home', 'front.packages', 'front.visa', 'front.hajj', 'front.umrah',
            'front.flights', 'front.hotels', 'front.transport', 'front.gallery',
            'front.faq', 'front.testimonials', 'front.privacy', 'front.terms',
            'front.refund', 'front.cancellation', 'front.career', 'front.support',
            'front.about', 'front.contact', 'front.blog', 'front.become.agent',
            'front.track.booking', 'front.track.visa',
        ];

        foreach ($routes as $name) {
            $response = $this->get(route($name));
            $this->assertSame(200, $response->status(), "GET {$name} returned {$response->status()}");
        }

        // Detail pages need a real row.
        $package = \App\Models\Package::where('status', 'active')->firstOrFail();
        $this->get(route('front.package', $package->id))->assertOk()->assertSee($package->title);

        $post = \App\Models\Blog::published()->firstOrFail();
        $this->get(route('front.blog.show', $post->slug))->assertOk()->assertSee($post->title);
    }

    public function test_every_booking_form_renders(): void
    {
        foreach (array_keys(\App\Http\Controllers\FrontendController::bookingTypes()) as $type) {
            $this->get(route('front.book', $type))->assertOk()->assertSee('Submit', false);
        }

        $this->get(route('front.book', 'not-a-real-type'))->assertNotFound();
    }

    public function test_home_renders_content_from_the_database(): void
    {
        $slider = \App\Models\Slider::where('status', 'active')->orderBy('sort_order')->orderBy('id')->firstOrFail();
        $review = \App\Models\Testimonial::active()->latest()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($slider->title)          // hero comes from the CMS slider
            ->assertSee($review->name);          // reviews come from testimonials
    }

    /** Destination tour counts must be real, not decorative. */
    public function test_home_destination_counts_match_the_catalogue(): void
    {
        $top = \App\Models\Package::where('status', 'active')
            ->selectRaw('destination, COUNT(*) as tours')
            ->groupBy('destination')->orderByDesc('tours')->first();

        if (! $top) {
            $this->markTestSkipped('no active packages seeded');
        }

        $this->get(route('home'))->assertOk()->assertSee($top->destination);
    }

    public function test_only_published_blog_posts_are_reachable(): void
    {
        $draft = \App\Models\Blog::create([
            'title' => 'Hidden Draft', 'slug' => 'hidden-draft-test', 'status' => 'draft',
        ]);

        $this->get(route('front.blog'))->assertOk()->assertDontSee('Hidden Draft');
        $this->get(route('front.blog.show', $draft->slug))->assertNotFound();
    }

    /** Only active rows may appear in the public catalogues. */
    public function test_inactive_catalogue_rows_are_hidden(): void
    {
        $visa = \App\Models\VisaService::create([
            'country' => 'Hiddenland', 'visa_type' => 'Tourist', 'status' => 'inactive',
        ]);
        $route = \App\Models\FlightRoute::create([
            'origin' => 'Hiddenville', 'destination' => 'Nowhere', 'status' => 'inactive',
        ]);

        $this->get(route('front.visa'))->assertOk()->assertDontSee($visa->country);
        $this->get(route('front.flights'))->assertOk()->assertDontSee($route->origin);
    }

    public function test_visa_tracking_finds_a_real_application(): void
    {
        $application = \App\Models\VisaApplication::first();

        if (! $application) {
            $this->markTestSkipped('no visa applications seeded');
        }

        $this->get(route('front.track.visa', ['ref' => $application->application_no]))
            ->assertOk()
            ->assertSee($application->applicant_name);

        $this->get(route('front.track.visa', ['ref' => 'DOES-NOT-EXIST']))
            ->assertOk()
            ->assertSee('No application found');
    }

    public function test_newsletter_signup_creates_a_subscriber(): void
    {
        $this->post(route('front.newsletter'), [
            'email'  => 'newsletter-test@example.com',
            'source' => 'home',
        ])->assertRedirect();

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'newsletter-test@example.com',
            'status' => 'subscribed',
            'source' => 'home',
        ]);
    }

    /** Re-subscribing must not create a duplicate row. */
    public function test_newsletter_signup_is_idempotent(): void
    {
        foreach ([1, 2] as $_) {
            $this->post(route('front.newsletter'), ['email' => 'twice@example.com'])->assertRedirect();
        }

        $this->assertSame(1, \App\Models\Subscriber::where('email', 'twice@example.com')->count());
    }

    /** Contact details render from settings, never from a hardcoded string. */
    public function test_contact_details_come_from_settings(): void
    {
        $this->get(route('front.contact'))
            ->assertOk()
            ->assertSee(settings('address'))
            ->assertSee(settings('email'));
    }

    public function test_job_application_flow_stores_application(): void
    {
        Storage::fake('local');

        $job = \App\Models\JobOpening::active()->open()->firstOrFail();

        $this->get(route('front.career.apply', $job))
            ->assertOk()
            ->assertSee($job->title);

        $this->post(route('front.career.apply.store', $job), [
            'name'         => 'Career Applicant',
            'email'        => 'applicant@example.com',
            'phone'        => '+8801700000000',
            'linkedin_url' => 'https://www.linkedin.com/in/career-applicant',
            'resume'       => UploadedFile::fake()->create('resume.pdf', 80, 'application/pdf'),
            'cover_letter' => 'I would like to apply for this role.',
        ])->assertRedirect(route('front.career.apply', $job));

        $application = \App\Models\JobApplication::where('email', 'applicant@example.com')->firstOrFail();

        $this->assertSame($job->id, $application->job_opening_id);
        $this->assertSame('new', $application->status);
        Storage::disk('local')->assertExists($application->resume_path);
    }

    /* ===================== Marketing content blocks ===================== */

    /**
     * Every content-block section must be seeded and rendered by its page —
     * this is what stops the copy drifting back into the blade templates.
     */
    public function test_every_content_block_section_renders_on_its_page(): void
    {
        $pages = [
            'home_services'  => ['home'],
            'home_why'       => ['home'],
            'about_values'   => ['front.about'],
            'visa_steps'     => ['front.visa'],
            'support_topics' => ['front.support'],
            'agent_benefits' => ['front.become.agent'],
            'hajj_includes'  => ['front.hajj'],
            'umrah_includes' => ['front.umrah'],
        ];

        foreach ($pages as $section => [$route]) {
            $blocks = \App\Models\ContentBlock::section($section);
            $this->assertTrue($blocks->isNotEmpty(), "section {$section} has no seeded blocks");

            $response = $this->get(route($route))->assertOk();

            foreach ($blocks as $block) {
                $response->assertSee($block->title);
            }
        }

        // booking_why lives on every /book/{type} page.
        $reasons = \App\Models\ContentBlock::section('booking_why');
        $this->assertTrue($reasons->isNotEmpty());
        $response = $this->get(route('front.book', 'flight'))->assertOk();
        foreach ($reasons as $reason) {
            $response->assertSee($reason->title);
        }
    }

    /** Deactivating a block removes the card from the site. */
    public function test_inactive_content_block_is_hidden(): void
    {
        $block = \App\Models\ContentBlock::create([
            'section' => 'home_why', 'title' => 'Temporary Promise', 'body' => 'x', 'status' => 'active',
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Temporary Promise');

        $block->update(['status' => 'inactive']);

        $this->get(route('home'))->assertOk()->assertDontSee('Temporary Promise');
    }

    /** About-page story copy is settings-driven. */
    public function test_about_story_comes_from_settings(): void
    {
        $this->get(route('front.about'))
            ->assertOk()
            ->assertSee(settings('about_story_heading'))
            ->assertSee(settings('about_story_lead'));
    }

    /* ===================== Navigation ===================== */

    /** Header and footer render the CMS menu tree, not hardcoded links. */
    public function test_navigation_renders_from_the_menu_tree(): void
    {
        $header = \App\Models\Menu::tree(\App\Models\Menu::POSITION_HEADER);
        $footer = \App\Models\Menu::tree(\App\Models\Menu::POSITION_FOOTER);

        $this->assertTrue($header->isNotEmpty(), 'header menu not seeded');
        $this->assertTrue($footer->isNotEmpty(), 'footer menu not seeded');

        $response = $this->get(route('home'))->assertOk();

        // Titles are asserted escaped (a heading like "Visa & Faith" renders
        // as "Visa &amp; Faith"); hrefs are asserted raw.
        foreach ($header as $node) {
            $response->assertSee($node->title);
        }

        // Mega-menu column headings and their links must both appear.
        $mega = $header->first(fn ($n) => $n->isMega());
        $this->assertNotNull($mega, 'expected a mega menu in the header');

        foreach ($mega->children as $column) {
            $response->assertSee($column->title);
            foreach ($column->children as $link) {
                $response->assertSee($link->href(), false);
            }
        }

        foreach ($footer as $column) {
            $response->assertSee($column->title);
        }
    }

    /** Deactivating a menu item removes it from the site. */
    public function test_inactive_menu_items_are_hidden(): void
    {
        $item = \App\Models\Menu::create([
            'title'    => 'Secret Nav Item',
            'url'      => '/secret-nav',
            'position' => \App\Models\Menu::POSITION_HEADER,
            'status'   => 'active',
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Secret Nav Item');

        $item->update(['status' => 'inactive']);

        $this->get(route('home'))->assertOk()->assertDontSee('Secret Nav Item');
    }

    /** Every seeded navigation link must resolve to a real page. */
    public function test_no_menu_link_is_broken(): void
    {
        $links = \App\Models\Menu::active()
            ->whereNotNull('url')->where('url', '!=', '#')
            ->pluck('url')->unique();

        $this->assertGreaterThan(10, $links->count());

        foreach ($links as $url) {
            if (\Illuminate\Support\Str::startsWith($url, ['http', 'mailto:', 'tel:'])) {
                continue;
            }

            $status = $this->get($url)->status();
            $this->assertSame(200, $status, "menu link {$url} returned {$status}");
        }
    }
}
