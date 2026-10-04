@php
    $appName = settings('name') ?: 'FLOW';
    $authBrandName = ___('auth.brand_name');

    // The brand panel is shared by login, registration and the staff sign-in,
    // but they are not selling the same thing: registration creates a customer
    // account, so the agency pitch and the agency-owner testimonial belong on
    // the other two. A page overrides what it needs and inherits the rest.
    // Sections are already collected by the time this layout renders.
    $authFeaturesRaw = trim($__env->yieldContent('brand_features')) ?: ___('auth.brand_features');
    $authQuoteText   = trim($__env->yieldContent('brand_quote_text')) ?: ___('auth.quote_text');
    $authQuoteName   = trim($__env->yieldContent('brand_quote_name')) ?: ___('auth.quote_name');
    $authQuoteRole   = trim($__env->yieldContent('brand_quote_role')) ?: ___('auth.quote_role');

    $authFeatures = collect(preg_split('/\r\n|\r|\n/', (string) $authFeaturesRaw))
        ->map(fn ($feature) => trim($feature))
        ->filter()
        ->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $appName }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ favicon(settings('favicon')) }}">
    <link href="{{ asset('backend/libs/bootstrap4/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/libs/fontawesome6/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('auth/css/saas-layout.css') }}?v={{ filemtime(public_path('auth/css/saas-layout.css')) }}" rel="stylesheet">
    @stack('styles')
    <style>
        .auth-flash { border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; font-size: 14px; line-height: 1.5; }
        .auth-flash--ok   { background: #e9f9ef; border: 1px solid #b7e7c9; color: #14713d; }
        .auth-flash--bad  { background: #fdecec; border: 1px solid #f5c2c2; color: #a4262c; }
        .auth-flash--warn { background: #fff8e1; border: 1px solid #f2dda4; color: #8a6100; }
        .auth-flash ul { margin: 0; }
        .social-login-buttons .social-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 42px;
            border-radius: 7px;
            font-weight: 500;
            letter-spacing: .01em;
            border: 0;
        }
        .social-login-btn .social-login-icon {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 20px;
            font-size: 16px;
            line-height: 1;
        }
        .social-login-btn--google { color: #13233d; background: #e5e7eb; }
        .social-login-btn--google:hover { color: #13233d; background: #d9dde3; }
        .social-login-btn--google .social-login-icon svg { width: 18px; height: 18px; }
        .social-login-btn--facebook { color: #fff; background: #050505; }
        .social-login-btn--facebook:hover { color: #fff; background: #1b1b1b; }
        .social-login-btn--facebook .social-login-icon { color: #050505; background: #fff; border-radius: 50%; }
    </style>
</head>
<body dir="{{ strtoupper(defaultLanguage()->text_direction ?? 'LTR') === 'LTR' ? 'ltr' : 'rtl' }}">

<div class="auth-wrap">

    <aside class="auth-brand">
        <div class="inner">
            {{-- The brand panel is dark, so prefer the dark-theme (light-ink)
                 logo and fall back to the light one, then to the wordmark. --}}
            @php
                $authLogoId = settings('dark_theme_logo') ?: settings('light_theme_logo');
                $authLogo   = $authLogoId ? \App\Models\Upload::find($authLogoId) : null;
                $authLogoOk = $authLogo && \Illuminate\Support\Facades\File::exists(public_path($authLogo->original));
            @endphp
            <a href="{{ route('home') }}" class="wordmark">
                @if($authLogoOk)
                    <img src="{{ asset($authLogo->original) }}" alt="{{ $authBrandName }}" class="brand-logo-img">
                @else
                    {{ $authBrandName }}<span>.</span>
                @endif
            </a>
            @yield('brand_heading')
            @if($authFeatures->isNotEmpty())
                <ul class="feat-list">
                    @foreach($authFeatures as $feature)
                        <li><span class="tick">{{ ___('auth.brand_feature_icon') }}</span> {{ $feature }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="quote-box">
            <div class="stars mb-1">{{ ___('auth.quote_stars') }}</div>
            <p>{{ $authQuoteText }}</p>
            <div class="who">{{ $authQuoteName }}</div>
            <div class="role">{{ $authQuoteRole }}</div>
        </div>
    </aside>

    <main class="auth-form-side">
        <div class="auth-card @yield('card_class')">
            {{-- Every auth screen redirects back with a flash on failure. Without
                 this block those messages were thrown away, so a failed password
                 reset looked exactly like nothing happening at all. --}}
            @foreach (['success' => 'ok', 'danger' => 'bad', 'error' => 'bad', 'warning' => 'warn'] as $key => $tone)
                @if (session()->has($key))
                    <div class="auth-flash auth-flash--{{ $tone }}" role="alert">{{ session($key) }}</div>
                @endif
            @endforeach

            @if ($errors->any() && $errors->count() > 1)
                <div class="auth-flash auth-flash--bad" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('form')
        </div>
    </main>

</div>

<script src="{{ asset('backend/libs/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('backend/libs/sweetalert2/js/sweetalert2.all.v11.min.js') }}"></script>
@include('backend.partials.alert-message')
<script src="{{ asset('backend/js/custom/flow-inline.js') }}?v={{ filemtime(public_path('backend/js/custom/flow-inline.js')) }}"></script>
@stack('scripts')
</body>
</html>
