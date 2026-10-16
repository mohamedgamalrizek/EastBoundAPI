<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactMessage;
use App\Models\ContentBlock;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Public marketing/help content for the app: FAQs, testimonials, and the
 * "Become an Agent" application (creates a CRM lead, same as the website).
 */
class ContentController extends Controller
{
    use ApiReturnFormatTrait;

    /** The subject options the website's contact form offers. */
    public const CONTACT_SUBJECTS = [
        'General enquiry',
        'Tour packages',
        'Booking support',
        'Visa services',
        'Hajj & Umrah',
    ];

    /** Languages the affiliate websites currently serve. */
    public const LANGUAGE_OPTIONS = Lead::LANGUAGES;

    /** Marketing sources accepted for affiliate lead attribution. */
    public const CAMPAIGN_SOURCE_OPTIONS = Lead::CAMPAIGN_SOURCES;

    /** FAQs grouped by category. Optional ?category= filter. */
    public function faqs(Request $request)
    {
        $query = Faq::where('status', 'active');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $faqs = $query->orderBy('category')->orderBy('id')->get();

        return $this->responseWithSuccess('FAQs fetched.', [
            'categories' => $faqs->pluck('category')->filter()->unique()->values(),
            'faqs'       => $faqs->map(fn (Faq $f) => [
                'id'       => $f->id,
                'question' => $f->question,
                'answer'   => $f->answer,
                'category' => $f->category,
            ]),
        ]);
    }

    public function testimonials()
    {
        $items = Testimonial::where('status', 'active')
            ->latest()
            ->get()
            ->map(fn (Testimonial $t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'role'    => $t->role,
                'message' => $t->message,
                'rating'  => (int) $t->rating,
            ]);

        return $this->responseWithSuccess('Testimonials fetched.', [
            'testimonials' => $items,
        ]);
    }

    /**
     * CMS content blocks for one section (?section=umrah_includes), so the app
     * renders the same editable copy the website does instead of hardcoding it.
     */
    public function contentBlocks(Request $request)
    {
        $section = (string) $request->input('section');

        if (! array_key_exists($section, ContentBlock::SECTIONS)) {
            return $this->responseWithError(
                'Unknown section. Allowed: ' . implode(', ', array_keys(ContentBlock::SECTIONS)),
                [],
                422
            );
        }

        $blocks = ContentBlock::section($section)->map(fn (ContentBlock $b) => [
            'id'    => $b->id,
            'icon'  => $b->icon,
            'title' => $b->title,
            'body'  => $b->body,
            'image' => $b->image ? asset($b->image) : null,
            'url'   => $b->href(),
        ]);

        return $this->responseWithSuccess('Content blocks fetched.', [
            'section' => $section,
            'blocks'  => $blocks,
        ]);
    }

    /**
     * Service enquiry — the app counterpart of the website's booking form
     * (/book/{type}). Files a CRM lead with every extra field folded into the
     * notes, exactly as FrontendController::bookingStore does.
     */
    public function enquiry(Request $request)
    {
        $types = \App\Http\Controllers\FrontendController::bookingTypes();

        $validator = Validator::make($request->all(), [
            'type'    => ['required', 'string', Rule::in(array_keys($types))],
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['required', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
            'details' => ['nullable', 'string', 'max:3000'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $label = $types[$request->type]['label'];

        $extra = collect($request->except(['type', 'name', 'phone', 'email', 'details']))
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . (is_array($v) ? implode(', ', $v) : $v))
            ->implode("\n");

        Lead::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'interest' => $label,
            // leads.source is an enum — app leads ride under Website and are
            // tagged in the notes so the CRM can still tell them apart.
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim("[Mobile App] {$label} request.\n" . $extra . "\n" . ($request->details ?? '')),
        ]);

        return $this->responseWithSuccess(
            "Thank you! Your {$label} request has been received. Our team will contact you shortly.",
            ['type' => $request->type],
            201
        );
    }

    /**
     * Contact details for the app's Contact screen — the same Settings values
     * the website's contact page renders, plus the subject list of its form.
     */
    public function contactInfo()
    {
        return $this->responseWithSuccess('Contact info fetched.', [
            'agency' => [
                'name'            => settings('name'),
                'address'         => settings('address'),
                'phone'           => settings('phone'),
                'phone_secondary' => settings('phone_secondary'),
                'email'           => settings('email'),
                'whatsapp'        => settings('whatsapp'),
            ],
            'subjects' => self::CONTACT_SUBJECTS,
            'languages' => self::LANGUAGE_OPTIONS,
            'campaign_sources' => self::CAMPAIGN_SOURCE_OPTIONS,
        ]);
    }

    /** Send a contact message — same record the website's contact form files. */
    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', Rule::in(self::LANGUAGE_OPTIONS)],
            'country' => ['nullable', 'string', 'max:255'],
            'campaign_source' => ['nullable', Rule::in(self::CAMPAIGN_SOURCE_OPTIONS)],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        DB::transaction(function () use ($request): void {
            $message = ContactMessage::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'phone'   => $request->phone,
                'subject' => $request->input('subject', self::CONTACT_SUBJECTS[0]),
                'website' => $request->input('website', $request->input('lead_source')),
                'language' => $request->language,
                'country' => $request->country,
                'campaign_source' => $request->campaign_source,
                'message' => $request->message,
                'status'  => 'new',
            ]);

            Lead::create([
                'name'     => $message->name,
                'phone'    => $message->phone,
                'email'    => $message->email,
                'interest' => $message->subject ?: 'General enquiry',
                'source'   => 'Website',
                'website'  => $message->website,
                'language' => $message->language,
                'country'  => $message->country,
                'campaign_source' => $message->campaign_source,
                'stage'    => 'New',
                'notes'    => trim($this->formatContactLeadNotes($message)),
            ]);
        });

        return $this->responseWithSuccess(
            'Thanks! Your message has been sent — we will get back to you soon.',
            [],
            201
        );
    }

    /**
     * Affiliate website lead intake. Accepts one lead object or an array of
     * lead objects and stores each as a CRM Lead.
     */
    public function leadIntake(Request $request)
    {
        $items = $this->normaliseLeadPayload($request->all());

        $validator = Validator::make(['leads' => $items], [
            'leads'                 => ['required', 'array', 'min:1', 'max:50'],
            'leads.*.name'          => ['required', 'string', 'max:255'],
            'leads.*.email'         => ['nullable', 'email', 'max:255'],
            'leads.*.phone'         => ['nullable', 'string', 'max:50'],
            'leads.*.website'       => ['required', 'string', 'max:255'],
            'leads.*.language'      => ['nullable', Rule::in(self::LANGUAGE_OPTIONS)],
            'leads.*.country'       => ['nullable', 'string', 'max:255'],
            'leads.*.campaign_source' => ['nullable', Rule::in(self::CAMPAIGN_SOURCE_OPTIONS)],
            'leads.*.subject'       => ['nullable', 'string', 'max:255'],
            'leads.*.destinations'  => ['nullable', 'string', 'max:255'],
            'leads.*.from_date'     => ['nullable', 'date'],
            'leads.*.to_date'       => ['nullable', 'date', 'after_or_equal:leads.*.from_date'],
            'leads.*.duration'      => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'leads.*.adults'        => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'leads.*.children'      => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'leads.*.budget'        => ['nullable', 'string', 'max:100'],
            'leads.*.value'         => ['nullable', 'numeric', 'min:0'],
            'leads.*.comments'      => ['nullable', 'string', 'max:5000'],
            'leads.*.created_at'    => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $created = DB::transaction(function () use ($items) {
            return collect($items)->map(function (array $item) {
                $subject = $item['subject'] ?: 'Tour packages';

                $lead = Lead::create([
                    'name'     => $item['name'],
                    'phone'    => $item['phone'],
                    'email'    => $item['email'],
                    'interest' => $item['destinations'] ?: $subject,
                    'source'   => 'Website',
                    'website'  => $item['website'],
                    'language' => $item['language'],
                    'country'  => $item['country'],
                    'campaign_source' => $item['campaign_source'],
                    'subject' => $subject,
                    'destinations' => $item['destinations'],
                    'from_date' => $item['from_date'],
                    'to_date' => $item['to_date'],
                    'duration' => $item['duration'],
                    'adults' => $item['adults'],
                    'children' => $item['children'],
                    'budget' => $item['budget'],
                    'comments' => $item['comments'],
                    'value'    => $item['value'] ?? 0,
                    'stage'    => 'New',
                    'notes'    => $this->formatAffiliateLeadNotes($item, $subject),
                ]);

                if ($item['created_at']) {
                    $lead->created_at = Carbon::parse($item['created_at']);
                    $lead->save();
                }

                return [
                    'id'       => $lead->id,
                    'name'     => $lead->name,
                    'website'  => $lead->website,
                    'language' => $lead->language,
                    'country'  => $lead->country,
                    'campaign_source' => $lead->campaign_source,
                    'interest' => $lead->interest,
                    'stage'    => $lead->stage,
                ];
            })->values();
        });

        return $this->responseWithSuccess('Lead intake completed.', [
            'count' => $created->count(),
            'leads' => $created,
        ], 201);
    }

    /** The service types the enquiry endpoint accepts, for the app's picker. */
    public function enquiryTypes()
    {
        $types = collect(\App\Http\Controllers\FrontendController::bookingTypes())
            ->map(fn ($cfg, $key) => [
                'key'   => $key,
                'label' => $cfg['label'],
                'icon'  => $cfg['icon'],
                'intro' => $cfg['intro'],
            ])->values();

        return $this->responseWithSuccess('Enquiry types fetched.', [
            'types' => $types,
        ]);
    }

    private function normaliseLeadPayload(array $payload): array
    {
        $items = array_is_list($payload) ? $payload : [$payload];

        return collect($items)->map(function (array $item) {
            return [
                'created_at'   => $item['created_at'] ?? null,
                'name'         => $item['full_name'] ?? $item['name'] ?? null,
                'email'        => $item['email'] ?? null,
                'phone'        => $item['phone'] ?? null,
                'website'      => $item['lead_source'] ?? $item['website'] ?? $item['site'] ?? null,
                'language'     => $item['language'] ?? null,
                'country'      => $item['country'] ?? null,
                'campaign_source' => $item['campaign_source'] ?? $item['campaign'] ?? null,
                'subject'      => $item['subject'] ?? null,
                'destinations' => $item['destinations'] ?? $item['destination'] ?? null,
                'from_date'    => $item['from_date'] ?? $item['start_date'] ?? null,
                'to_date'      => $item['to_date'] ?? $item['end_date'] ?? null,
                'duration'     => $item['duration'] ?? null,
                'adults'       => $item['adults'] ?? null,
                'children'     => $item['children'] ?? null,
                'budget'       => $item['budget'] ?? null,
                'value'        => $item['value'] ?? $item['estimated_value'] ?? null,
                'comments'     => $item['comments'] ?? $item['message'] ?? $item['details'] ?? null,
            ];
        })->all();
    }

    private function formatAffiliateLeadNotes(array $item, string $subject): string
    {
        return collect([
            'Affiliate lead',
            'Lead Source / Website: ' . $item['website'],
            'Language: ' . ($item['language'] ?: 'Not specified'),
            'Country: ' . ($item['country'] ?: 'Not specified'),
            'Campaign Source: ' . ($item['campaign_source'] ?: 'Not specified'),
            'Subject: ' . $subject,
            'Destinations: ' . ($item['destinations'] ?: 'Not specified'),
            'From Date: ' . ($item['from_date'] ?: 'Not specified'),
            'To Date: ' . ($item['to_date'] ?: 'Not specified'),
            'Duration: ' . ($item['duration'] !== null ? $item['duration'] : 'Not specified'),
            'Adults: ' . ($item['adults'] !== null ? $item['adults'] : 'Not specified'),
            'Children: ' . ($item['children'] !== null ? $item['children'] : 'Not specified'),
            'Budget: ' . ($item['budget'] ?: 'Not specified'),
            'Original Created At: ' . ($item['created_at'] ?: 'Not specified'),
            '',
            $item['comments'],
        ])->filter(fn ($line) => $line !== null)->implode("\n");
    }

    private function formatContactLeadNotes(ContactMessage $message): string
    {
        return collect([
            "Contact message #{$message->id}",
            'Lead Source / Website: ' . ($message->website ?: 'Not specified'),
            'Language: ' . ($message->language ?: 'Not specified'),
            'Country: ' . ($message->country ?: 'Not specified'),
            'Campaign Source: ' . ($message->campaign_source ?: 'Not specified'),
            '',
            $message->message,
        ])->implode("\n");
    }

    /** Become an Agent — files a CRM lead for the partnerships team. */
    public function becomeAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        Lead::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'interest' => 'Agent Partnership',
            // leads.source is an enum (Facebook|Website|Referral|WhatsApp|
            // Instagram|Walk-in) — app leads ride under Website and are tagged
            // in the notes so the CRM can still tell them apart.
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim('[Mobile App] Become an Agent application. ' . ($request->notes ?? '')),
        ]);

        return $this->responseWithSuccess(
            'Application received. Our partnerships team will contact you shortly.',
            [],
            201
        );
    }
}
