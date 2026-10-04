<?php

/*
|--------------------------------------------------------------------------
| SaaS marketing/landing content
|--------------------------------------------------------------------------
| Everything the public landing + signup pages render is driven from here,
| so the whole SaaS front-end can be re-branded / re-worded without touching
| any blade. Merged into the `saas-landing` config key by SaasServiceProvider;
| read in views via config('saas-landing.*').
|
| Brand name + tagline fall back to env() so a deployment can white-label
| without editing the file.
*/

return [

    'brand'   => env('SAAS_BRAND', 'FLOW'),
    'tagline' => env('SAAS_TAGLINE', 'The all-in-one platform for modern travel agencies.'),

    'meta' => [
        'title'       => 'FLOW — Run your travel agency on autopilot',
        'description' => 'FLOW is the all-in-one travel-agency platform: bookings, visa, hotels, flights, Hajj, accounting and CRM — each agency on its own private, secure workspace.',
    ],

    'hero' => [
        'pill'         => '⚡ All-in-one travel-agency platform',
        'headline'     => 'Run your travel agency<br>on <em>autopilot</em>.',
        'lead'         => 'Bookings, visa, hotels, flights, Hajj, accounting and CRM — every agency on its own private, secure workspace. Launch in minutes, no setup, no servers.',
        'primary_cta'  => 'Start your workspace',
        'secondary_cta' => 'See pricing',
        'benefits'     => '✓ No credit card to start &nbsp;·&nbsp; ✓ Your own database &nbsp;·&nbsp; ✓ Cancel anytime',
    ],

    // The "Agencies onboard" stat stays dynamic (live tenant count); these are
    // the three static platform stats next to it.
    'stats' => [
        ['value' => '40+',   'label' => 'Built-in modules'],
        ['value' => '12',    'label' => 'Payment gateways'],
        ['value' => '99.9%', 'label' => 'Isolated & secure'],
    ],

    'features_head' => [
        'title' => 'Everything your agency needs',
        'text'  => 'One platform replaces a dozen tools. Built specifically for travel agencies — from a single desk to a full back-office.',
    ],

    'features' => [
        ['icon' => '🧳', 'title' => 'Packages & Bookings',    'text' => 'Build tour packages, take bookings end-to-end, track every traveler and payment in one place.'],
        ['icon' => '🛂', 'title' => 'Visa & Hajj',            'text' => 'Manage visa applications, documents, appointments and expiries — plus full Hajj & Umrah packages and pilgrims.'],
        ['icon' => '🏨', 'title' => 'Hotels & Flights',       'text' => 'Hotel rooms and bookings, flight PNRs, transport — all your inventory and reservations together.'],
        ['icon' => '📊', 'title' => 'Accounting & Reports',   'text' => 'Chart of accounts, invoices, receipts, and 9 reports with date filters and CSV export.'],
        ['icon' => '👥', 'title' => 'CRM & Portals',          'text' => 'Leads to conversion, plus self-service portals for your customers, agents and staff.'],
        ['icon' => '🔒', 'title' => 'Private & Secure',       'text' => 'Your own isolated database, role-based access, and a full audit trail of every change.'],
    ],

    'how_head' => [
        'title' => 'Live in three steps',
        'text'  => 'No installs, no servers, no waiting. Pick a plan and your workspace is ready before your coffee.',
    ],

    'steps' => [
        ['title' => 'Choose a plan',       'text' => 'Pick the tier that fits your team size and the features you need.'],
        ['title' => 'Claim your address',  'text' => 'Get your own subdomain like <b>youragency</b>.:domain with a private database.'],
        ['title' => 'Start selling',       'text' => 'Log in to your workspace and run bookings, visa, accounting — everything, day one.'],
    ],

    'pricing_head' => [
        'title' => 'Simple, transparent pricing',
        'text'  => 'Pay for what your agency needs. Upgrade, downgrade or cancel anytime.',
    ],

    'gateways_label' => 'Accept payments your customers already use',
    'gateways' => ['bKash', 'Nagad', 'SSLCOMMERZ', 'aamarPay', 'Razorpay', 'PayU', 'Cashfree', 'Instamojo', 'Stripe', 'PayPal', 'Paystack', 'Flutterwave'],

    'testimonials_head' => 'Loved by travel agencies',
    'testimonials' => [
        ['quote' => 'FLOW replaced four separate tools. Our bookings and accounting finally talk to each other.', 'name' => 'Rahim Uddin',  'role' => 'Owner, Skyline Travels'],
        ['quote' => 'The visa and Hajj modules alone saved us a full-time coordinator during peak season.',         'name' => 'Ayesha Karim', 'role' => 'Manager, Globe Tours'],
        ['quote' => 'Setup took ten minutes and we had our own private workspace. Support has been excellent.',     'name' => 'Tanvir Hasan', 'role' => 'Director, Nomad Agency'],
    ],

    'faq_head' => 'Frequently asked questions',
    'faqs' => [
        ['q' => 'Is my data separate from other agencies?', 'a' => 'Yes. Every agency runs on its own isolated database — your records are never mixed with anyone else’s.'],
        ['q' => 'How fast can I get started?',              'a' => 'Minutes. Choose a plan, pick your subdomain, and your workspace is provisioned on the spot with an owner login.'],
        ['q' => 'Which payments can I accept?',             'a' => 'Twelve gateways out of the box — bKash, Nagad, SSLCOMMERZ, aamarPay, Razorpay, PayU, Cashfree, Instamojo, Stripe, PayPal, Paystack and Flutterwave.'],
        ['q' => 'Can I change plans later?',                'a' => 'Absolutely. Upgrade or downgrade anytime from your billing settings; changes apply immediately.'],
        ['q' => 'Do you support Bangla?',                   'a' => 'Yes — the entire interface ships in both English and Bangla, and more languages can be added.'],
    ],

    'cta' => [
        'title' => 'Ready to grow your agency?',
        'text'  => 'Join the agencies running their whole business on FLOW. Your workspace is one click away.',
    ],
];
