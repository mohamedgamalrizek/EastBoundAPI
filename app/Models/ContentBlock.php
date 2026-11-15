<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * A reusable icon + title + text card on the public site, grouped by `section`.
 */
class ContentBlock extends Model
{
    /**
     * Every section the public site renders, with the label shown in the admin.
     * Adding a key here makes it selectable; the view must render it too.
     */
    public const SECTIONS = [
        'website_header' => 'Shared — Header logo and CTA',
        'website_footer' => 'Shared — Footer text, links and contact',
        'website_home_hero' => 'Home — Hero',
        'website_home_destinations' => 'Home — Destination cards',
        'website_home_tours' => 'Home — Tours heading',
        'website_home_tailor_made' => 'Home — Tailor Made introduction',
        'website_home_why' => 'Home — Why choose us',
        'website_home_cta' => 'Home — Call to action',
        'website_about_hero' => 'About — Hero',
        'website_about_story' => 'About — Story and images',
        'website_about_values' => 'About — Values',
        'website_about_cta' => 'About — Call to action',
        'website_destinations_hero' => 'Destinations — Hero',
        'website_destinations_intro' => 'Destinations — Intro and CTA',
        'website_tours_hero' => 'Tours — Hero and CTA',
        'website_country_why' => 'Country pages — Why travel with us',
        'website_country_cta' => 'Country pages — Call to action',
        'website_tailor_made_hero' => 'Tailor Made — Hero',
        'website_tailor_made_values' => 'Tailor Made — Benefits',
        'website_tailor_made_steps' => 'Tailor Made — Steps',
        'website_tailor_made_inspiration' => 'Tailor Made — Inspiration cards',
        'website_tailor_made_cta' => 'Tailor Made — Call to action',
        'website_contact_hero' => 'Contact — Hero',
        'website_contact_benefits' => 'Contact — Benefits',
        'website_magazine_hero' => 'Magazine — Hero',
        'website_faq_hero' => 'FAQ — Hero',
        'home_services'  => 'Home — “Everything for your journey” grid',
        'home_why'       => 'Home — “Why us” list',
        'about_values'   => 'About — our values',
        'visa_steps'     => 'Visa — how it works (4 steps)',
        'support_topics' => 'Support — help topic cards',
        'agent_benefits' => 'Become an Agent — benefits',
        'hajj_includes'  => 'Hajj — what’s included',
        'umrah_includes' => 'Umrah — what’s included',
        'booking_why'    => 'Booking form — why book with us',
    ];

    protected $fillable = [
        'section', 'icon', 'title', 'body', 'url', 'image', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Active blocks for one section, in display order. */
    public static function section(string $section)
    {
        return static::active()->where('section', $section)
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Resolve the link. Accepts a route name (front.visa), a site path
     * (/visa-services) or an absolute URL; returns null when there's no link.
     */
    public function href(): ?string
    {
        $url = trim((string) $this->url);

        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://', 'mailto:', 'tel:', '#', '/'])) {
            return Str::startsWith($url, '/') ? url($url) : $url;
        }

        // Bare value: treat it as a route name, falling back to a path.
        return Route::has($url) ? route($url) : url($url);
    }
}
