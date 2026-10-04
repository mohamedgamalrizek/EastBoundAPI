@php
    $metaTitle = trim($__env->yieldContent('title')) ?: ___('frontend.default_meta_title');
    $metaDesc  = trim($__env->yieldContent('meta')) ?: ___('frontend.default_meta_description');
    $ogImage   = trim($__env->yieldContent('og_image')) ?: asset('frontend/images/og-default.jpg');
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $metaTitle = trim($__env->yieldContent('title')) ?: (settings('seo_meta_title') ?: $metaTitle);
    $metaDesc = trim($__env->yieldContent('meta')) ?: (settings('seo_meta_description') ?: $metaDesc);
    $ogImage = trim($__env->yieldContent('og_image')) ?: (settings('og_image') ? logo(settings('og_image')) : $ogImage);
    $metaKeywords = settings('seo_meta_keywords');
    $googleAnalyticsId = trim((string) settings('google_analytics_id'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#A81A20">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('flow-theme');
                var theme = savedTheme === 'light' || savedTheme === 'dark'
                    ? savedTheme
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        }());
    </script>

    {{-- Primary SEO --}}
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    @if($metaKeywords)
    <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="index, follow">
    <meta name="author" content="FLOW">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="FLOW">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Favicon — from backend settings, falls back to the bundled svg --}}
    @if($googleAnalyticsId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $googleAnalyticsId }}');
    </script>
    @endif

    @php $siteFavicon = settings('favicon') ? favicon(settings('favicon')) : asset('frontend/images/favicon.svg'); @endphp
    <link rel="icon" href="{{ $siteFavicon }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ $siteFavicon }}">

    {{-- Fonts (self-hosted in public/fonts, no external requests) --}}
    <link href="{{ asset('fonts/fonts.css') }}" rel="stylesheet">

    {{-- Vendor CSS --}}
    <link href="{{ asset('backend/libs/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/fontawesome6/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/aos/css/aos.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/css/flag-icons/flag-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    {{-- Searchable selects (From/To, country, nationality…). A newer version
         than the admin panel's bundled copy — the Bootstrap 5 theme needs
         4.0.13+ to render correctly, so this pair lives in select2-4.1/
         rather than reusing backend/libs/select2. --}}
    <link href="{{ asset('backend/libs/select2-4.1/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/select2-4.1/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet">

    {{-- FLOW design system --}}
    <link href="{{ asset('frontend/scss/main.css') }}?v={{ filemtime(public_path('frontend/scss/main.css')) }}" rel="stylesheet">
    @php
        $primary = settings('frontend_primary_color') ?: '#A81A20';
        $secondary = settings('frontend_secondary_color') ?: '#F7E7E8';
        $appearance = [
            '--tv-primary' => $primary,
            '--tv-primary-dark' => 'color-mix(in srgb, var(--tv-primary) 78%, #000)',
            '--tv-primary-50' => 'color-mix(in srgb, var(--tv-primary) 7%, #fff)',
            '--tv-primary-100' => 'color-mix(in srgb, var(--tv-primary) 18%, #fff)',
            '--tv-primary-200' => 'color-mix(in srgb, var(--tv-primary) 38%, #fff)',
            '--tv-primary-soft' => 'color-mix(in srgb, var(--tv-primary) 12%, transparent)',
            '--tv-selection-bg' => 'color-mix(in srgb, var(--tv-primary) 12%, transparent)',
            '--tv-selection-text' => 'color-mix(in srgb, var(--tv-primary) 78%, #000)',
            '--tv-info' => $primary,
            '--tv-secondary' => $secondary,
            '--tv-font' => '"' . (settings('frontend_font_family') ?: 'Inter') . '", system-ui, sans-serif',
            '--tv-button-radius' => (int) (settings('frontend_button_radius') ?? 999) . 'px',
            '--tv-card-radius' => (int) (settings('frontend_card_radius') ?? 20) . 'px',
            '--tv-input-radius' => (int) (settings('frontend_input_radius') ?? 8) . 'px',
            '--tv-input-border-width' => (int) (settings('frontend_input_border_width') ?? 1) . 'px',
            '--tv-input-border-style' => settings('frontend_input_border_style') ?: 'solid',
        ];
    @endphp
    <style>:root{@foreach($appearance as $name => $value){{ $name }}:{{ $value }};@endforeach}body{font-family:var(--tv-font)}.btn{border-radius:var(--tv-button-radius)}.card,.service-card{border-radius:var(--tv-card-radius)}.form-control,.form-select{border-width:var(--tv-input-border-width);border-style:var(--tv-input-border-style);border-radius:var(--tv-input-radius)}.service-card .sc-icon,.feature-item .fi-icon{color:var(--tv-primary-dark);background:var(--tv-secondary)}</style>
    @stack('styles')

    {{-- Organization schema — every value comes from Settings, nothing hardcoded --}}
    @php
        $schema = array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'TravelAgency',
            'name'        => settings('name') ?: config('app.name'),
            'url'         => url('/'),
            'logo'        => settings('light_theme_logo') ? logo(settings('light_theme_logo')) : asset('frontend/images/favicon.svg'),
            'description' => $metaDesc,
            'address'     => settings('address') ? ['@type' => 'PostalAddress', 'streetAddress' => settings('address')] : null,
            'telephone'   => settings('phone') ?: null,
            'email'       => settings('email') ?: null,
            'sameAs'      => \App\Models\SocialLink::active()->ordered()->pluck('url')->values()->all() ?: null,
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @stack('schema')
</head>
<body data-page="@yield('page', '')">
{{-- <body data-page="@yield('page', '')"> --}}

    @include('frontend.partials.header')

    <main id="top">
        @yield('content')
    </main>

    @include('frontend.partials.footer')

    {{-- Floating actions. The WhatsApp bubble only appears once a number is
         configured in Settings → General. --}}
    @php $waNumber = preg_replace('/\D/', '', (string) (settings('whatsapp') ?: settings('phone'))); @endphp
    @if($waNumber)
    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="wa-float" aria-label="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    @endif
    <button class="scroll-top" id="scrollTop" aria-label="{{ ___('frontend.back_to_top') }}"><i class="fa-solid fa-arrow-up"></i></button>

    {{-- Vendor JS --}}
    <script src="{{ asset('backend/libs/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('backend/libs/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/libs/aos/js/aos.js') }}"></script>
    <script src="{{ asset('backend/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('backend/libs/select2-4.1/js/select2.min.js') }}"></script>
    <script src="{{ asset('backend/libs/flatpickr/flatpickr.min.js') }}"></script>

    {{-- FLOW scripts --}}
    <script src="{{ asset('frontend/js/app.js') }}?v={{ filemtime(public_path('frontend/js/app.js')) }}"></script>
    <script src="{{ asset('frontend/js/wishlist.js') }}?v={{ filemtime(public_path('frontend/js/wishlist.js')) }}"></script>
    @stack('scripts')
</body>
</html>
