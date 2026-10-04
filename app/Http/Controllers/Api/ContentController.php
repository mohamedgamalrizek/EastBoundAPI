<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactMessage;
use App\Models\ContentBlock;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
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
        'Booking support',
        'Visa services',
        'Hajj & Umrah',
    ];

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
                'message' => $request->message,
                'status'  => 'new',
            ]);

            Lead::create([
                'name'     => $message->name,
                'phone'    => $message->phone,
                'email'    => $message->email,
                'interest' => $message->subject ?: 'General enquiry',
                'source'   => 'Website',
                'stage'    => 'New',
                'notes'    => trim("Contact message #{$message->id}\n\n{$message->message}"),
            ]);
        });

        return $this->responseWithSuccess(
            'Thanks! Your message has been sent — we will get back to you soon.',
            [],
            201
        );
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
