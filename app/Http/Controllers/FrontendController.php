<?php

namespace App\Http\Controllers;

use App\Actions\OpenTourBookingFromEnquiry;
use App\Services\Booking\BookingPricing;
use App\Actions\OpenTransportBookingFromEnquiry;
use App\Actions\OpenVisaCaseForBooking;
use App\Actions\OpenVisaCaseFromEnquiry;
use App\Actions\RegisterHajjPilgrim;
use App\Models\Blog;
use App\Models\Booking;
use App\Models\CmsPage;
use App\Models\ContentBlock;
use App\Models\Customer;
use App\Models\Faq;
use App\Models\FlightRoute;
use App\Models\Gallery;
use App\Models\HajjPackage;
use App\Models\Hotel;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Slider;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\TransportBooking;
use App\Models\TransportService;
use App\Models\VehicleCategory;
use App\Models\VisaApplication;
use App\Models\VisaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\JobApplication\StoreJobApplicationRequest;
use App\Repositories\ContactMessage\ContactMessageInterface;
use App\Repositories\CustomerPortal\CustomerPortalInterface;
use App\Http\Requests\ContactMessage\StoreContactMessageRequest;
use App\Traits\ApiTransformTrait;
use App\Traits\ResolvesCustomer;

/**
 * Public marketing website.
 *
 * Every page here reads its content from the database so the agency can edit
 * the site from the admin CMS — nothing user-visible is hardcoded in a blade.
 * Where a list can legitimately be empty (no sliders configured yet, no fare
 * deals published) the view degrades gracefully rather than showing a gap.
 */
class FrontendController extends Controller
{
    use ApiTransformTrait, ResolvesCustomer;

    protected $messages;
    protected $portal;

    public function __construct(ContactMessageInterface $messages, CustomerPortalInterface $portal)
    {
        $this->messages = $messages;
        $this->portal = $portal;
    }

    /* ===================== Home ===================== */

    public function home()
    {
        // In SaaS mode the root domain is the product front door: show the
        // SaaS marketing landing (hero, features, live pricing) instead of a
        // single agency's public site. Single mode keeps the agency website.
        if (config('saas.enabled')) {
            return app(\Modules\Saas\Http\Controllers\SaasController::class)->landing();
        }

        $page = CmsPage::published()->where('slug', '/')->first();

        // Sliders drive the hero here the same way they do in /api/v1/home;
        // the website reads the real `image` column rather than image_label.
        return view('frontend.home', [
            'page'         => $page,
            'slides'       => Slider::where('status', 'active')->orderBy('sort_order')->orderBy('id')->get(),
            'destinations' => $this->popularDestinations(4),
            'packages'     => Package::where('status', 'active')->latest()->take(6)->get(),
            'wishlisted'   => $this->portal->wishlistedPackageIds(),
            'reviews'      => Testimonial::active()->latest()->take(6)->get(),
            'posts'        => Blog::published()->latest('published_at')->take(3)->get(),
            'stats'        => $this->siteStats($page),
            'categories'   => $this->packageCategories(),
            'hotelCities'  => Hotel::active()->distinct()->orderBy('city')->pluck('city'),
            'flightCities' => $this->flightRouteCities(),
            'flightFares'  => $this->flightRouteFares(),
            'visaCountries'=> VisaService::active()->ordered()->pluck('country')->unique()->values(),
            // Airlines we actually publish fares for — powers the partners strip.
            'airlines'     => $this->partnerAirlines(),
            'services'     => ContentBlock::section('home_services'),
            'whyUs'        => ContentBlock::section('home_why'),
        ]);
    }

    // Newsletter sign-up lives in newsletterStore() below — it writes to the
    // dedicated `subscribers` table rather than the CRM lead pipeline.

    /**
     * Trending destinations, derived from the live package catalogue so the
     * tour counts on the home page are real rather than decorative.
     */
    private function popularDestinations(int $limit)
    {
        $rows = Package::where('status', 'active')
            ->select('destination')
            ->selectRaw('COUNT(*) as tours')
            ->selectRaw('MAX(id) as sample_id')
            ->groupBy('destination')
            ->orderByDesc('tours')
            ->take($limit)
            ->get();

        // One extra query gives each destination a representative image and
        // sub-label, instead of N queries inside the view.
        $samples = Package::whereIn('id', $rows->pluck('sample_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($samples) {
            $sample = $samples->get($row->sample_id);

            return (object) [
                'name'     => $row->destination,
                'tours'    => (int) $row->tours,
                'category' => $sample->category ?? null,
                'image'    => $sample->image ?? null,
            ];
        });
    }

    /**
     * Headline numbers shown on the home and about pages.
     *
     * Each is computed from live data, and each can be overridden from
     * Settings → General when the agency wants to publish a marketing figure
     * (e.g. lifetime travelers carried over from before this system).
     * Returned pre-split into number + suffix for the count-up animation.
     */
    private function siteStats(?CmsPage $page = null): array
    {
        $visaTotal    = VisaApplication::count();
        $visaApproved = VisaApplication::where('status', 'Approved')->count();

        return collect([
            ['value' => $page?->stat_travelers    ?: Customer::count() . '+',                                          'label' => 'Happy travelers'],
            ['value' => $page?->stat_destinations ?: Package::where('status', 'active')->distinct()->count('destination') . '+', 'label' => 'Destinations'],
            ['value' => $page?->stat_visa_success ?: ($visaTotal ? round($visaApproved / $visaTotal * 100) . '%' : '-'), 'label' => 'Visa success'],
            ['value' => $page?->stat_experience   ?: '10+ yrs',                                                        'label' => 'Of experience'],
        ])->map(fn ($stat) => $this->statParts($stat['value']) + ['label' => $stat['label']])->all();
    }

    /**
     * Airlines we publish fares for, each paired with its logo when the agency
     * has supplied one.
     *
     * Logos are looked up by slug in `public/frontend/img/airlines/` (svg, then
     * png/webp/jpg) — drop `emirates.svg`, `qatar-airways.svg` etc. in there and
     * they appear automatically. Airline marks are trademarks, so none ship with
     * the app; anything missing falls back to a clean text lockup.
     */
    private function partnerAirlines()
    {
        $names = FlightRoute::active()->whereNotNull('airline')
            ->distinct()->orderBy('airline')->pluck('airline');

        return $names->map(function (string $name) {
            $slug = Str::slug($name);
            $logo = null;

            foreach (['svg', 'png', 'webp', 'jpg'] as $ext) {
                $relative = "frontend/img/airlines/{$slug}.{$ext}";
                if (file_exists(public_path($relative))) {
                    $logo = asset($relative);
                    break;
                }
            }

            return (object) ['name' => $name, 'logo' => $logo];
        });
    }

    /** Split a display figure such as "12K+" or "98%" into number and suffix. */
    private function statParts(string $value): array
    {
        preg_match('/^\s*([\d.,]*)(.*)$/u', $value, $m);

        return [
            'num'    => $m[1] !== '' ? $m[1] : '0',
            'suffix' => trim($m[2] ?? ''),
        ];
    }

    /* ===================== Tour packages ===================== */

    public function packages(Request $request)
    {
        $query = Package::where('status', 'active');

        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('destination', 'like', "%{$q}%");
            });
        }

        // The filter posts a category id; an old bookmarked link that still
        // carries the category name keeps working.
        if ($category = $request->input('category')) {
            is_numeric($category)
                ? $query->where('category_id', $category)
                : $query->where('category', $category);
        }

        match ($request->input('sort')) {
            'price_low'  => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            default      => $query->latest(),
        };

        return view('frontend.packages', [
            'packages'   => $query->paginate(9)->withQueryString(),
            'categories' => $this->packageCategories(),
            'wishlisted' => $this->portal->wishlistedPackageIds(),
        ]);
    }

    /**
     * The Package Categories an admin manages, limited to the ones that
     * actually have a live package — an empty filter option helps nobody.
     */
    private function packageCategories()
    {
        return PackageCategory::where('status', 'active')
            ->whereHas('packages', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function packageShow($id)
    {
        // approvedReviews carries both the list shown lower down the page and
        // the star rating in the header — Package::$avg_rating reads it from
        // the loaded relation rather than running its own query.
        $package = Package::with(['itineraries', 'approvedReviews.customer'])->findOrFail($id);

        // The guide leading the next departure, when one is assigned.
        $guide = optional(\App\Models\TourGuideAssignment::with('guide.ratings')
            ->where('package_id', $package->id)
            ->active()
            ->where('end_date', '>=', today())
            ->orderBy('start_date')
            ->first())->guide;

        $related = Package::where('status', 'active')
            ->where('id', '!=', $package->id)
            ->where(function ($w) use ($package) {
                $w->where('category_id', $package->category_id)
                  ->orWhere('destination', $package->destination);
            })
            ->latest()->take(4)->get();

        if ($related->isEmpty()) {
            $related = Package::where('status', 'active')
                ->where('id', '!=', $package->id)
                ->latest()->take(4)->get();
        }

        $wishlisted = $this->portal->wishlistedPackageIds();

        return view('frontend.package-show', compact('package', 'related', 'guide', 'wishlisted'));
    }

    // Public booking request from a package page -> creates a real Booking (pending)
    public function bookStore(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'travel_date'    => ['required', 'date', 'after_or_equal:today'],
            'travelers'      => ['required', 'integer', 'min:1', 'max:50'],
            'needs_visa'     => ['nullable', 'boolean'],
            // A code from the website visitor. Loyalty points are not offered
            // here: the booker may not be signed in, and spending somebody's
            // points needs them to be.
            'coupon_code'    => ['nullable', 'string', 'max:60'],
        ]);

        $customer = $this->resolveCustomer($data);

        $pricing = app(BookingPricing::class);
        $quote   = $pricing->quote((float) $package->price * (int) $data['travelers'], null, $request->coupon_code);

        $booking = Booking::create([
            'package_id'     => $package->id,
            // Attach the booking to a customer record, otherwise it lands in the
            // admin list orphaned: invisible on the customer's profile and in
            // their portal's "My Bookings".
            'customer_id'    => $customer->id,
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'],
            'travel_date'    => $data['travel_date'],
            'travelers'      => $data['travelers'],
            'status'         => 'pending',
            'notes'          => 'Booking requested from website.',
        ] + $pricing->columns($quote));

        $lost = $pricing->commit($booking, $quote);

        if ($request->boolean('needs_visa')) {
            app(OpenVisaCaseForBooking::class)($booking, $package, $customer);
        }

        $message = 'Booking request submitted! Our team will contact you shortly to confirm.';

        if ($quote['coupon_discount'] > 0 && ! $lost) {
            $message .= ' Promo code ' . $quote['coupon_code'] . ' saved you '
                . currency_symbol() . number_format($quote['coupon_discount'], 2) . '.';
        }

        return redirect()
            ->route('front.package', $package->id)
            ->with('success', trim($message . ' ' . ($quote['coupon_error'] ?? '') . ' ' . implode(' ', $lost)));
    }

    /* ===================== Services ===================== */

    /** Visa landing — the published visa catalogue (not customer applications). */
    public function visa(Request $request)
    {
        $query = VisaService::active()->ordered();

        if ($q = $request->input('q')) {
            $query->where('country', 'like', "%{$q}%");
        }

        if ($type = $request->input('type')) {
            $query->where('visa_type', $type);
        }

        return view('frontend.visa', [
            'steps'     => ContentBlock::section('visa_steps'),
            'services'  => $query->get(),
            'types'     => VisaService::active()->distinct()->orderBy('visa_type')->pluck('visa_type'),
            'countries' => VisaService::active()->distinct()->orderBy('country')->pluck('country'),
        ]);
    }

    /** Hajj landing — real packages from the Hajj module. */
    public function hajj()
    {
        return view('frontend.hajj', [
            'packages' => $this->hajjPackages('Hajj'),
            'includes' => ContentBlock::section('hajj_includes'),
        ]);
    }

    /** Umrah landing — real packages from the Hajj module. */
    public function umrah()
    {
        return view('frontend.umrah', [
            'packages' => $this->hajjPackages('Umrah'),
            'includes' => ContentBlock::section('umrah_includes'),
        ]);
    }

    private function hajjPackages(string $type)
    {
        return HajjPackage::where('status', 'active')
            ->where('type', $type)
            ->orderBy('price')
            ->get();
    }

    /** Hotel landing — the same inventory the ERP manages, filtered for the public. */
    public function hotels(Request $request)
    {
        $query = Hotel::active();

        if ($city = $request->input('city')) {
            $query->where(function ($w) use ($city) {
                $w->where('city', 'like', "%{$city}%")
                  ->orWhere('country', 'like', "%{$city}%")
                  ->orWhere('name', 'like', "%{$city}%");
            });
        }

        if ($stars = $request->input('stars')) {
            $query->where('category', $stars);
        }

        return view('frontend.hotels', [
            'hotels' => $query->orderByDesc('is_featured')->orderByDesc('category')->get(),
            'cities' => Hotel::active()->distinct()->orderBy('city')->pluck('city'),
        ]);
    }

    /** Flight landing — published fare deals. */
    public function flights()
    {
        return view('frontend.flights', [
            'routes'       => FlightRoute::active()->ordered()->get(),
            'flightCities' => $this->flightRouteCities(),
            'flightFares'  => $this->flightRouteFares(),
        ]);
    }

    /**
     * from/to/fare rows for the flight forms' live estimate, keyed by the
     * same bare city names the From/To selects use. Just a hint — the form
     * still only opens a lead; the real fare is whatever staff quote once a
     * ticket is actually booked.
     */
    private function flightRouteFares()
    {
        return FlightRoute::active()
            ->get(['origin', 'destination', 'fare'])
            ->map(fn ($r) => ['from' => $r->origin, 'to' => $r->destination, 'fare' => (float) $r->fare])
            ->values();
    }

    /**
     * country/visa_type/fee rows for the visa application form's live
     * estimate. Same caveat as the flight fare: a hint, not a locked price —
     * the form still only opens a case, and OpenVisaCaseFromEnquiry quotes
     * the real fee from this same catalogue when the case is created.
     */
    private function visaServiceFees()
    {
        return VisaService::active()
            ->get(['country', 'visa_type', 'govt_fee', 'service_fee'])
            ->map(fn ($s) => ['country' => $s->country, 'visa_type' => $s->visa_type, 'fee' => $s->totalFee()])
            ->values();
    }

    /**
     * City/code options for the From/To flight selects, drawn from both ends
     * of every published route — a route's destination is another search's
     * origin, so one combined list serves both fields. The select's value is
     * the bare city name (what "Book" links on /flight-booking already pass
     * as ?from=/?to=), the code is shown alongside it as the label.
     */
    private function flightRouteCities()
    {
        return FlightRoute::active()
            ->get(['origin', 'origin_code', 'destination', 'destination_code'])
            ->flatMap(fn ($r) => [
                (object) ['city' => $r->origin, 'code' => $r->origin_code],
                (object) ['city' => $r->destination, 'code' => $r->destination_code],
            ])
            ->unique('city')
            ->sortBy('city')
            ->values();
    }

    /**
     * Transport landing.
     *
     * The catalogue is CMS-managed (Transport Services), but the "from" price
     * prefers what the agency has actually booked for that vehicle type — a
     * real floor beats a configured guess. Services with no bookings fall back
     * to their configured starting price.
     */
    public function transport()
    {
        // transport_bookings.type is Bus | Train | Launch | Car | Airport.
        $bookedFrom = TransportBooking::selectRaw('type, MIN(fare) as from_fare')
            ->groupBy('type')
            ->pluck('from_fare', 'type');

        $services = TransportService::active()->ordered()->get()
            ->each(fn (TransportService $service) => $service->setAttribute(
                'booked_from',
                $service->vehicle_type ? ($bookedFrom[$service->vehicle_type] ?? null) : null
            ));

        $vehicleCategories = VehicleCategory::active()->ordered()->pluck('name');

        return view('frontend.transport', compact('services', 'vehicleCategories'));
    }

    /* ===================== Content pages ===================== */

    public function about()
    {
        $page = CmsPage::published()->where('slug', 'about-us')->first();

        return view('frontend.about', [
            'page'   => $page,
            'stats'  => $this->siteStats($page),
            'values' => ContentBlock::section('about_values'),
        ]);
    }

    public function gallery(Request $request)
    {
        $query = Gallery::where('status', 'active');

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        return view('frontend.gallery', [
            'images'     => $query->orderBy('sort_order')->orderByDesc('id')->get(),
            'categories' => Gallery::where('status', 'active')
                ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function faq(Request $request)
    {
        $query = Faq::where('status', 'active');

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        return view('frontend.faq', [
            'faqs'       => $query->orderBy('category')->orderBy('id')->get(),
            'categories' => Faq::where('status', 'active')
                ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function testimonials()
    {
        return view('frontend.testimonials', [
            'reviews' => Testimonial::active()->latest()->paginate(12),
        ]);
    }

    public function career()
    {
        return view('frontend.career', [
            'jobs' => JobOpening::active()->open()->ordered()->get(),
        ]);
    }

    public function careerApply(JobOpening $job)
    {
        abort_unless($job->status === 'active' && (! $job->closing_date || $job->closing_date->isToday() || $job->closing_date->isFuture()), 404);

        return view('frontend.career-apply', compact('job'));
    }

    public function careerApplyStore(StoreJobApplicationRequest $request, JobOpening $job)
    {
        abort_unless($job->status === 'active' && (! $job->closing_date || $job->closing_date->isToday() || $job->closing_date->isFuture()), 404);

        $resumePath = $request->hasFile('resume')
            ? $request->file('resume')->store('job-applications', 'local')
            : null;

        JobApplication::create([
            'job_opening_id' => $job->id,
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'linkedin_url'   => $request->linkedin_url,
            'resume_path'    => $resumePath,
            'cover_letter'   => $request->cover_letter,
            'status'         => 'new',
        ]);

        return redirect()
            ->route('front.career.apply', $job)
            ->with('success', 'Thanks! Your application has been submitted. We will review it and contact you soon.');
    }

    public function support()
    {
        return view('frontend.support', [
            'topics' => ContentBlock::section('support_topics'),
            'faqs'   => Faq::where('status', 'active')->latest()->take(6)->get(),
        ]);
    }

    public function contact()
    {
        return view('frontend.contact', [
            'page' => CmsPage::published()->where('slug', 'contact-us')->first(),
        ]);
    }

    // Public contact form -> stores a ContactMessage inbox row and a CRM Lead.
    public function contactStore(StoreContactMessageRequest $request)
    {
        $result = $this->messages->store($request);
        $page = CmsPage::published()->where('slug', 'contact-us')->first();

        return redirect()
            ->route('front.contact')
            ->with($result['status'] ? 'success' : 'danger',
                $result['status'] ? ($page?->success_message ?: 'Thanks! Your message has been sent - we will get back to you soon.') : $result['message']);
    }

    /* ===================== Legal / CMS pages ===================== */

    public function privacy()      { return $this->cmsPage('privacy-policy',      'Privacy Policy'); }
    public function terms()        { return $this->cmsPage('terms-conditions',    'Terms & Conditions'); }
    public function refund()       { return $this->cmsPage('refund-policy',       'Refund Policy'); }
    public function cancellation() { return $this->cmsPage('cancellation-policy', 'Cancellation Policy'); }

    /**
     * Render a CMS-managed static page. Bodies are authored as Markdown and
     * rendered with raw HTML stripped, so admin content can never inject
     * script into the public site.
     */
    private function cmsPage(string $slug, string $fallbackTitle)
    {
        $page = CmsPage::published()->where('slug', $slug)->first();

        return view('frontend.cms-page', [
            'page'  => $page,
            'title' => $page->title ?? $fallbackTitle,
            'body'  => $page && filled($page->body)
                ? Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false])
                : null,
        ]);
    }

    /* ===================== Customers & newsletter ===================== */

    /**
     * Website Hajj / Umrah registration.
     *
     * Goes through the same action the mobile app uses, so a registration made
     * here shows up in Hajj > Manage Pilgrims with its package, passport and
     * outstanding amount — previously the form only left a CRM lead and the
     * Hajj module never saw it at all.
     */
    private function hajjRegistrationStore(Request $request, string $type)
    {
        $data = $request->validate([
            'hajj_package_id' => ['required', 'integer', 'exists:hajj_packages,id'],
            'passport_no'     => ['required', 'string', 'max:60'],
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
        ]);

        $package = HajjPackage::where('status', 'active')
            ->where('type', ucfirst($type))
            ->find($data['hajj_package_id']);

        if (! $package) {
            return back()->withInput()->with('danger', 'That package is no longer available.');
        }

        $customer = $this->resolveCustomer([
            'customer_name'  => $data['name'],
            'customer_email' => $data['email'] ?? null,
            'customer_phone' => $data['phone'],
        ]);

        try {
            $pilgrim = app(RegisterHajjPilgrim::class)(
                $package,
                $customer,
                $request->only([
                    'name', 'passport_no', 'phone', 'email',
                    'package_pref', 'pilgrims', 'preferred_month', 'room_sharing', 'details',
                ]),
                'Website'
            );
        } catch (\DomainException $e) {
            return back()->withInput()->with('danger', $e->getMessage());
        }

        return redirect()->route('front.book', $type)->with(
            'success',
            "Registration received. Your pilgrim reference is {$pilgrim->pilgrim_no} — our consultant will contact you about documents and payment."
        );
    }

    /**
     * Website visa application.
     *
     * Opens a real case in Visa > Applications through the same action the
     * mobile app uses. It used to leave only a CRM lead, so the applicant was
     * told "request received" while the visa module never heard about it.
     */
    private function visaApplicationStore(Request $request)
    {
        $data = $request->validate([
            'country'     => ['required', 'string', 'max:120'],
            'visa_type'   => ['required', 'string', 'max:120'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:255'],
            'details'     => ['nullable', 'string', 'max:3000'],
        ]);

        $customer = $this->resolveCustomer([
            'customer_name'  => $data['name'],
            'customer_email' => $data['email'] ?? null,
            'customer_phone' => $data['phone'],
        ]);

        $application = app(OpenVisaCaseFromEnquiry::class)($customer, [
            'name'        => $data['name'],
            'phone'       => $data['phone'],
            'email'       => $data['email'] ?? null,
            'details'     => $data['details'] ?? null,
            'country'     => $data['country'],
            'visa_type'   => $data['visa_type'],
            'nationality' => $data['nationality'] ?? null,
            'travel_date' => $request->input('travel_date'),
        ]);

        // The answers with no column of their own (nationality, travel date,
        // free text) stay with the consultant as a lead.
        \App\Models\Lead::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'email'    => $data['email'] ?? null,
            'interest' => 'Visa Application',
            'source'   => 'Website',
            'stage'    => 'New',
            // Nullable answers are absent from the validated array, not null,
            // when the applicant leaves them blank.
            'notes'    => trim("[Website] Visa application {$application->application_no}.\n"
                . "Country: {$application->country} ({$application->visa_type})\n"
                . (($data['nationality'] ?? null) ? "Nationality: {$data['nationality']}\n" : '')
                . ($request->input('travel_date') ? "Travel date: {$request->input('travel_date')}\n" : '')
                . ($data['details'] ?? '')),
        ]);

        return redirect()->route('front.book', 'visa')->with(
            'success',
            "Application received. Your reference is {$application->application_no} — we'll contact you about the documents to send."
        );
    }

    /**
     * Website airport transfer / car rental request.
     *
     * Opens a real Transport > Manage booking through the same shape the
     * mobile app's own transport request uses (Pending, fare priced later by
     * the desk) — see OpenTransportBookingFromEnquiry.
     */
    private function transportBookingStore(Request $request, string $type)
    {
        if ($type === 'car-rental') {
            $data = $request->validate([
                'vehicle'     => ['nullable', 'string', 'max:120'],
                'with_driver' => ['nullable', 'string', 'max:60'],
                'pickup_date' => ['required', 'date', 'after_or_equal:today'],
                'return_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
                'city'        => ['required', 'string', 'max:190'],
                'name'        => ['required', 'string', 'max:255'],
                'phone'       => ['required', 'string', 'max:50'],
                'email'       => ['nullable', 'email', 'max:255'],
                'details'     => ['nullable', 'string', 'max:3000'],
            ]);

            $transportType = 'Car';
            $direction     = null;
            $route         = 'Rental pickup: ' . $data['city'];
            $travelDate    = $data['pickup_date'];
        } else {
            $data = $request->validate([
                'pickup'  => ['required', 'string', 'max:190'],
                'dropoff' => ['required', 'string', 'max:190'],
                'date'    => ['required', 'date', 'after_or_equal:today'],
                'time'    => ['nullable', 'string', 'max:20'],
                'vehicle' => ['nullable', 'string', 'max:120'],
                'name'    => ['required', 'string', 'max:255'],
                'phone'   => ['required', 'string', 'max:50'],
                'email'   => ['nullable', 'email', 'max:255'],
                'details' => ['nullable', 'string', 'max:3000'],
            ]);

            $transportType = 'Airport';
            // The type tells you the vehicle class, not which way the
            // passenger is going — this is what the admin list shows to tell
            // "meet them at arrivals" apart from "take them to check-in".
            $direction     = $type === 'airport-drop' ? 'Drop' : 'Pickup';
            $route         = "{$data['pickup']} → {$data['dropoff']}";
            $travelDate    = $data['date'];
        }

        $customer = $this->resolveCustomer([
            'customer_name'  => $data['name'],
            'customer_email' => $data['email'] ?? null,
            'customer_phone' => $data['phone'],
        ]);

        $trip = app(OpenTransportBookingFromEnquiry::class)($customer, array_merge($data, [
            'type'        => $transportType,
            'direction'   => $direction,
            'route'       => $route,
            'travel_date' => $travelDate,
        ]));

        return redirect()->route('front.book', $type)->with(
            'success',
            "Request received. Your reference is {$trip->booking_no} — we'll confirm the fare and vehicle shortly."
        );
    }

    /**
     * Website tour / custom tour enquiry.
     *
     * Opens a real (pending, unpriced) Booking through OpenTourBookingFromEnquiry
     * instead of leaving only a CRM lead — see that action for why package_id
     * is often null here.
     */
    private function tourBookingStore(Request $request, string $type)
    {
        $data = $request->validate([
            'from'           => ['nullable', 'string', 'max:190'],
            'to'             => ['nullable', 'string', 'max:190'],
            'package'        => ['nullable', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date'    => ['nullable', 'date', 'after_or_equal:departure_date'],
            'travelers'      => ['nullable', 'integer', 'min:1', 'max:50'],
            'name'           => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'details'        => ['nullable', 'string', 'max:3000'],
        ]);

        $customer = $this->resolveCustomer([
            'customer_name'  => $data['name'],
            'customer_email' => $data['email'] ?? null,
            'customer_phone' => $data['phone'],
        ]);

        $booking = app(OpenTourBookingFromEnquiry::class)($customer, $data);

        return redirect()->route('front.book', $type)->with(
            'success',
            "Request received. Our team will contact you shortly to confirm your trip (Booking #{$booking->id})."
        );
    }

    /**
     * The customer behind a public booking: the signed-in portal user's own
     * record when there is one, otherwise an existing match on email/phone,
     * otherwise a fresh record so the agency can follow up.
     */
    private function resolveCustomer(array $data): Customer
    {
        if (auth()->check() && auth()->user()->customer) {
            return auth()->user()->customer;
        }

        // Matching and creation live in ResolvesCustomer so the API's agent
        // bookings attach their client the same way this form does.
        return $this->resolveCustomerFromContact($data, 'Created from a website booking request.');
    }

    /** Newsletter sign-up from the public site. Re-subscribing is idempotent. */
    public function newsletterStore(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = Subscriber::firstOrNew(['email' => $validated['email']]);
        $alreadyActive = $subscriber->exists && $subscriber->status === 'subscribed';

        $subscriber->fill([
            'status'        => 'subscribed',
            'source'        => $request->input('source', 'website'),
            'subscribed_at' => $subscriber->subscribed_at ?? now(),
        ])->save();

        $message = $alreadyActive
            ? 'You are already subscribed - thanks for staying with us!'
            : 'Thanks! You are subscribed to FLOW deals.';

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message)->withFragment('newsletter');
    }

    /* ===================== Blog ===================== */

    public function blog(Request $request)
    {
        $page = CmsPage::published()->where('slug', 'blog')->first();
        $query = Blog::published();

        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        return view('frontend.blog', [
            'page'       => $page,
            'posts'      => $query->latest('published_at')->paginate(9)->withQueryString(),
            'categories' => Blog::published()
                ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function blogShow($slug)
    {
        $post = Blog::published()->where('slug', $slug)->firstOrFail();

        $related = Blog::published()
            ->where('id', '!=', $post->id)
            ->when($post->category, fn ($q) => $q->where('category', $post->category))
            ->latest('published_at')->take(3)->get();

        if ($related->isEmpty()) {
            $related = Blog::published()->where('id', '!=', $post->id)
                ->latest('published_at')->take(3)->get();
        }

        return view('frontend.blog-show', [
            'post'    => $post,
            'related' => $related,
            'body'    => filled($post->body)
                ? Str::markdown($post->body, ['html_input' => 'strip', 'allow_unsafe_links' => false])
                : null,
        ]);
    }

    /* ===================== Become an Agent ===================== */

    public function becomeAgent()
    {
        return view('frontend.become-agent', [
            'page'     => CmsPage::published()->where('slug', 'become-an-agent')->first(),
            'benefits' => ContentBlock::section('agent_benefits'),
        ]);
    }

    public function becomeAgentStore(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        \App\Models\Lead::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'email'    => $data['email'] ?? null,
            'interest' => 'Agent Partnership',
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim('Become an Agent application. ' . ($data['notes'] ?? '')),
        ]);

        $page = CmsPage::published()->where('slug', 'become-an-agent')->first();

        return redirect()->route('front.become.agent')
            ->with('success', $page?->success_message ?: 'Application received! Our partnerships team will reach out to you shortly.');
    }

    /* ===================== Tracking ===================== */

    public function trackBooking(Request $request)
    {
        $results = collect();
        $searched = false;

        if ($phone = $request->input('phone')) {
            $searched = true;
            $results = Booking::with('package')
                ->where('customer_phone', trim($phone))
                ->latest()->get();
        }

        return view('frontend.track-booking', compact('results', 'searched'));
    }

    /**
     * Look a visa application up by its reference number and render a real
     * progress timeline derived from its status and document state.
     */
    public function trackVisa(Request $request)
    {
        $application = null;
        $searched    = false;

        if ($ref = trim((string) $request->input('ref'))) {
            $searched    = true;
            $application = VisaApplication::where('application_no', $ref)->first();
        }

        return view('frontend.track-visa', [
            'searched'    => $searched,
            'application' => $application,
            'timeline'    => $application ? $this->visaTimeline($application) : [],
        ]);
    }

    /**
     * Map a VisaApplication onto the five public milestones. Exactly one stage
     * is "active" (the current one) unless the case is finished or rejected.
     */
    private function visaTimeline(VisaApplication $app): array
    {
        $docsDone   = in_array($app->documents_status, ['Submitted', 'Verified'], true);
        $lodged     = (bool) $app->applied_date;
        $decided    = in_array($app->status, ['Approved', 'Rejected'], true);
        $approved   = $app->status === 'Approved';

        $stages = [
            [
                'title' => 'Documents received',
                'time'  => $docsDone ? $app->documents_status : 'Awaiting your documents',
                'done'  => $docsDone,
            ],
            [
                'title' => 'Application lodged',
                'time'  => $lodged ? 'Filed on ' . dateFormat($app->applied_date) : 'Not filed yet',
                'done'  => $lodged,
            ],
            [
                'title' => 'Under processing',
                'time'  => $app->appointment_date
                    ? 'Appointment ' . dateFormat($app->appointment_date)
                    : 'Embassy review',
                'done'  => $decided,
            ],
            [
                'title' => 'Decision',
                'time'  => $decided ? $app->status : 'Awaiting outcome',
                'done'  => $decided,
            ],
            [
                'title' => $approved ? 'Visa ready' : 'Collection',
                'time'  => $approved
                    ? ($app->expiry_date ? 'Valid until ' . dateFormat($app->expiry_date) : 'Ready to collect')
                    : 'Pending approval',
                'done'  => $approved,
            ],
        ];

        // First not-yet-done stage becomes the highlighted "in progress" step.
        $markedActive = false;

        return array_map(function ($stage) use (&$markedActive) {
            $stage['state'] = $stage['done'] ? 'done' : (! $markedActive ? 'active' : '');
            $markedActive   = $markedActive || ! $stage['done'];

            return $stage;
        }, $stages);
    }

    /* ===================== Booking forms ===================== */

    // Config for every booking form type: label, icon, intro and which optional field groups to show.
    public static function bookingTypes(): array
    {
        return [
            'flight'         => ['label' => 'Flight Booking',     'icon' => 'fa-plane',          'intro' => 'Tell us your route and dates — we’ll find the best fare.',            'fields' => ['trip']],
            'hotel'          => ['label' => 'Hotel Booking',      'icon' => 'fa-hotel',          'intro' => 'Share your destination and stay dates for a tailored quote.',          'fields' => ['stay']],
            'visa'           => ['label' => 'Visa Application',   'icon' => 'fa-passport',       'intro' => 'Start your visa application — we’ll guide you on documents.',          'fields' => ['visa']],
            'tour'           => ['label' => 'Tour Booking',       'icon' => 'fa-umbrella-beach', 'intro' => 'Book a tour package and we’ll confirm the details with you.',          'fields' => ['trip']],
            'hajj'           => ['label' => 'Hajj Registration',  'icon' => 'fa-kaaba',          'intro' => 'Register your interest for Hajj — our consultant will contact you.',   'fields' => ['pilgrim']],
            'umrah'          => ['label' => 'Umrah Registration', 'icon' => 'fa-mosque',         'intro' => 'Register for an Umrah package with your preferred dates.',             'fields' => ['pilgrim']],
            'custom-tour'    => ['label' => 'Custom Tour Request','icon' => 'fa-route',          'intro' => 'Dream it and we’ll build it — describe your ideal trip.',             'fields' => ['trip']],
            'airport-pickup' => ['label' => 'Airport Pickup',     'icon' => 'fa-plane-arrival',  'intro' => 'Book a meet-and-greet pickup from the airport.',                      'fields' => ['transfer']],
            'airport-drop'   => ['label' => 'Airport Drop',       'icon' => 'fa-plane-departure','intro' => 'Book a comfortable drop-off to the airport.',                         'fields' => ['transfer']],
            'car-rental'     => ['label' => 'Car Rental',         'icon' => 'fa-car-side',       'intro' => 'Rent a vehicle with or without a driver.',                            'fields' => ['rental']],
        ];
    }

    public function bookingForm($type)
    {
        $types = self::bookingTypes();
        abort_unless(isset($types[$type]), 404);

        return view('frontend.booking-form', [
            'type'   => $type,
            'config' => $types[$type],
            // Real options so the enquiry form matches what we actually sell.
            'visaCountries' => VisaService::active()->ordered()->pluck('country')->unique()->values(),
            'packages'      => Package::where('status', 'active')->orderBy('title')->get(['id', 'title', 'destination']),
            'flightCities'  => $type === 'flight' ? $this->flightRouteCities() : collect(),
            'flightFares'   => $type === 'flight' ? $this->flightRouteFares() : collect(),
            'visaFees'      => $type === 'visa' ? $this->visaServiceFees() : collect(),
            'nationalities' => $type === 'visa' ? config('nationalities') : [],
            // Hajj / Umrah registration books a real package, so the form
            // offers the catalogue rather than an Economy/Standard/Premium
            // guess the Hajj module could not match to anything.
            'hajjPackages'  => in_array($type, ['hajj', 'umrah'], true)
                ? $this->hajjPackages(ucfirst($type))
                : collect(),
            'whyBook'          => ContentBlock::section('booking_why'),
            'vehicleCategories' => VehicleCategory::active()->ordered()->pluck('name'),
        ]);
    }

    public function bookingStore(Request $request, $type)
    {
        $types = self::bookingTypes();
        abort_unless(isset($types[$type]), 404);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['required', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
            'details' => ['nullable', 'string', 'max:3000'],
        ]);

        // Hajj and Umrah are registrations, not enquiries: they belong in the
        // Hajj module as a pilgrim, owing the package price. Everything else on
        // this form is a request for a quote, which is a CRM lead.
        if (in_array($type, ['hajj', 'umrah'], true)) {
            return $this->hajjRegistrationStore($request, $type);
        }

        // A visa application is a case the desk works, not an enquiry.
        if ($type === 'visa') {
            return $this->visaApplicationStore($request);
        }

        // Airport transfers and car rental are real jobs for the Transport
        // desk, not just an enquiry: they belong in Transport > Manage.
        if (in_array($type, ['airport-pickup', 'airport-drop', 'car-rental'], true)) {
            return $this->transportBookingStore($request, $type);
        }

        // A tour enquiry is a real (if unpriced) booking for the desk to
        // work — the same record the package page's "Book Now" button raises.
        if (in_array($type, ['tour', 'custom-tour'], true)) {
            return $this->tourBookingStore($request, $type);
        }

        // Build a readable notes block from all submitted fields (minus the contact basics).
        $extra = collect($request->except(['_token', 'name', 'phone', 'email', 'details']))
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v))
            ->implode("\n");

        \App\Models\Lead::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'email'    => $data['email'] ?? null,
            'interest' => $types[$type]['label'],
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim($types[$type]['label'] . " request.\n" . $extra . "\n" . ($data['details'] ?? '')),
        ]);

        return redirect()->route('front.book', $type)
            ->with('success', 'Thank you! Your ' . $types[$type]['label'] . ' request has been received. Our team will contact you shortly.');
    }
}
