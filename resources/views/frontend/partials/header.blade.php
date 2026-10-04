{{-- ============================================================
     Header — top bar + sticky navbar + mega menu + mobile drawer
     ============================================================ --}}
@php
    // Safe route resolver: returns the URL if the named route exists, else '#'
    $r = fn ($name, ...$args) => \Illuminate\Support\Facades\Route::has($name) ? route($name, ...$args) : '#';

    // Contact details come from General Settings; social links are managed separately.
    $sitePhone = settings('phone');
    $siteEmail = settings('email');
    $siteTel   = $sitePhone ? preg_replace('/[^\d+]/', '', $sitePhone) : null;
    $homePage  = \App\Models\CmsPage::published()->where('slug', '/')->first();
    $currentLanguage = defaultLanguage();
    $topSocial = \App\Models\SocialLink::active()->ordered()->get();
@endphp

{{-- Top utility bar --}}
<div class="top-bar d-none d-md-block">
    <div class="container">
        <div class="top-bar-inner">
            <div class="tb-left">
                @if($sitePhone)<a href="tel:{{ $siteTel }}" class="tb-item"><i class="fa-solid fa-phone"></i> {{ $sitePhone }}</a>@endif
                @if($siteEmail)<a href="mailto:{{ $siteEmail }}" class="tb-item"><i class="fa-solid fa-envelope"></i> {{ $siteEmail }}</a>@endif
                @if(settings('address_short') ?: settings('address'))
                    <span class="tb-item"><i class="fa-solid fa-location-dot"></i> {{ settings('address_short') ?: settings('address') }}</span>
                @endif
            </div>
            <div class="tb-right">
                <a href="{{ $r('front.track.booking') }}" class="tb-item"><i class="fa-solid fa-magnifying-glass-location"></i> {{ ___('frontend.track_booking') }}</a>
                @if($topSocial->isNotEmpty())
                <span class="tb-social">
                    @foreach($topSocial as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener" aria-label="{{ $social->name }}"><i class="fa-brands {{ $social->icon }}"></i></a>
                    @endforeach
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Main header --}}
<header class="site-header" id="siteHeader">
    <div class="container">
        <nav class="navbar-tv">
            {{-- Brand — use the matching configured logo for each color theme --}}
            <a class="brand" href="{{ route('home') }}">
                @if(settings('light_theme_logo'))
                    <img src="{{ logo(settings('light_theme_logo')) }}" alt="{{ settings('name') ?: 'FLOW' }}" class="brand-img {{ settings('dark_theme_logo') ? 'brand-img--light' : '' }}">
                    @if(settings('dark_theme_logo'))
                        <img src="{{ logo(settings('dark_theme_logo')) }}" alt="{{ settings('name') ?: 'FLOW' }}" class="brand-img brand-img--dark">
                    @endif
                @else
                    <span class="brand-logo"><i class="fa-solid fa-paper-plane"></i></span>
                    <span class="brand-name">Travel<span>io</span></span>
                @endif
            </a>

            {{-- Desktop nav — built from CMS → Manage Menus (position: header).
                 A root with grandchildren renders as a mega menu, a root with
                 plain children as a dropdown, a childless root as a link. --}}
            <ul class="main-nav">
                @foreach($headerMenu as $node)
                    @php $isCurrent = $node->isCurrent() || $node->hasCurrentChild(); @endphp

                    @if($node->children->isEmpty())
                        <li>
                            <a class="nav-link {{ $isCurrent ? 'active' : '' }}" href="{{ $node->href() }}" @if($node->target) target="{{ $node->target }}" rel="noopener" @endif>
                                @if($node->icon)<i class="fa-solid {{ $node->icon }}"></i> @endif{{ $node->title }}
                            </a>
                        </li>

                    @elseif($node->isMega())
                        <li class="has-mega dropdown-mega">
                            <a class="nav-link {{ $isCurrent ? 'active' : '' }}" href="{{ $node->href() }}">{{ $node->title }} <i class="fa-solid fa-chevron-down caret"></i></a>
                            <div class="mega-menu">
                                <div class="row g-4">
                                    @foreach($node->children as $column)
                                    <div class="col-lg-3 col-6">
                                        <div class="mega-col-title">{{ $column->title }}</div>
                                        @foreach($column->children as $link)
                                            <a class="mega-link" href="{{ $link->href() }}" @if($link->target) target="{{ $link->target }}" rel="noopener" @endif>
                                                @if($link->icon)<i class="fa-solid {{ $link->icon }}"></i> @endif{{ $link->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                    @endforeach

                                    {{-- Optional promo panel, set in Settings → General. --}}
                                    @if($homePage?->promo_title)
                                    <div class="col-lg-3 d-none d-lg-block">
                                        <div class="mega-promo">
                                            @if($homePage->promo_badge)<span class="badge-tv mb-2 align-self-start">{{ $homePage->promo_badge }}</span>@endif
                                            <h5>{{ $homePage->promo_title }}</h5>
                                            @if($homePage->promo_text)<p>{{ $homePage->promo_text }}</p>@endif
                                            <a href="{{ $homePage->promo_link ?: route('front.packages') }}" class="btn btn-white btn-sm align-self-start">{{ ___('frontend.explore') }}</a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </li>

                    @else
                        <li class="has-dropdown">
                            <a class="nav-link {{ $isCurrent ? 'active' : '' }}" href="{{ $node->href() }}">{{ $node->title }} <i class="fa-solid fa-chevron-down caret"></i></a>
                            <div class="nav-dropdown">
                                @foreach($node->children as $link)
                                    <a href="{{ $link->href() }}" @if($link->target) target="{{ $link->target }}" rel="noopener" @endif>{{ $link->title }}</a>
                                @endforeach
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>

            {{-- Actions --}}
            <div class="header-actions">
                <button class="icon-action" id="searchOpen" aria-label="{{ ___('frontend.search') }}"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button class="icon-action theme-toggle" id="themeToggle" type="button" aria-label="{{ ___('frontend.switch_to_dark_mode') }}" aria-pressed="false" title="{{ ___('frontend.switch_to_dark_mode') }}">
                    <i class="fa-solid fa-moon" aria-hidden="true"></i>
                </button>
                {{-- Language switcher --}}
                @if(isset($languages) && $languages->count())
                    <div class="lang-switch d-none d-xl-block">
                        <button class="icon-action lang-current" aria-label="{{ ___('menus.language') }}">
                            @if($currentLanguage?->icon_class)
                                <i class="{{ $currentLanguage->icon_class }}"></i>
                            @else
                                <i class="fa-solid fa-globe"></i>
                            @endif
                        </button>
                        <div class="lang-menu">
                            @foreach($languages as $lang)
                                <a href="{{ route('setLocalization', $lang->code) }}">
                                    @if($currentLanguage?->code === $lang->code)
                                        <i class="fa-solid fa-check text-success"></i>
                                    @else
                                        <span class="lang-placeholder"></span>
                                    @endif
                                    @if($lang->icon_class)
                                        <i class="{{ $lang->icon_class }}"></i>
                                    @endif
                                    {{ $lang->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($siteTel)
                <a href="tel:{{ $siteTel }}" class="call-btn d-none d-xxl-inline-flex">
                    <span class="ca-icon"><i class="fa-solid fa-phone"></i></span>
                    <span>{{ ___('frontend.call_us') }}</span>
                </a>
                @endif

                <span class="auth-actions d-none d-lg-inline-flex gap-2">
                    @auth
                        {{-- home() routes each role to the panel it can actually open:
                             admin → /dashboard, agent → agent portal, customer → customer portal. --}}
                        <a href="{{ auth()->user()->home() }}" class="btn btn-soft btn-sm">{{ ___('frontend.dashboard') }}</a>
                    @else
                        <a href="{{ route('loginForm') }}" class="btn btn-ghost btn-sm">{{ ___('frontend.login') }}</a>
                        <a href="{{ route('registerForm') }}" class="btn btn-brand btn-sm">{{ ___('frontend.register') }}</a>
                    @endauth
                </span>

                <button class="nav-toggler" id="navToggler" aria-label="{{ ___('frontend.menu') }}"><i class="fa-solid fa-bars"></i></button>
            </div>
        </nav>
    </div>
</header>

{{-- Search overlay --}}
<div class="search-overlay" id="searchOverlay">
    <button class="icon-action search-close" id="searchClose" aria-label="{{ ___('frontend.close_search') }}"><i class="fa-solid fa-xmark"></i></button>
    <form class="search-box" action="{{ route('front.packages') }}" method="get">
        <p class="eyebrow-text mb-2">{{ ___('frontend.search_flow') }}</p>
        <input type="text" name="q" class="form-control search-input" placeholder="{{ ___('frontend.search_placeholder') }}">
        <p class="text-muted-2 mt-2 text-14">{{ ___('frontend.search_hint') }}</p>
    </form>
</div>

{{-- Mobile drawer --}}
<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="mobile-drawer" id="mobileDrawer">
    <div class="md-head">
        <a class="brand" href="{{ route('home') }}">
            @if(settings('light_theme_logo'))
                <img src="{{ logo(settings('light_theme_logo')) }}" alt="{{ settings('name') ?: 'FLOW' }}" class="brand-img {{ settings('dark_theme_logo') ? 'brand-img--light' : '' }}">
                @if(settings('dark_theme_logo'))
                    <img src="{{ logo(settings('dark_theme_logo')) }}" alt="{{ settings('name') ?: 'FLOW' }}" class="brand-img brand-img--dark">
                @endif
            @else
                <span class="brand-logo"><i class="fa-solid fa-paper-plane"></i></span>
                <span class="brand-name">Travel<span>io</span></span>
            @endif
        </a>
        <button class="icon-action" id="drawerClose" aria-label="{{ ___('frontend.close') }}"><i class="fa-solid fa-xmark"></i></button>
    </div>
    {{-- Mobile drawer — same CMS tree, flattened one level: a mega menu's
         columns are merged into a single collapsible group. --}}
    <div class="md-body">
        @foreach($headerMenu as $node)
            @if($node->children->isEmpty())
                <a class="md-link" href="{{ $node->href() }}" @if($node->target) target="{{ $node->target }}" rel="noopener" @endif>{{ $node->title }}</a>
            @else
                @php
                    // Grandchildren when it's a mega menu, children otherwise.
                    $links = $node->isMega()
                        ? $node->children->flatMap->children
                        : $node->children;
                @endphp
                <div>
                    <a class="md-link" data-collapse="#mdNav{{ $node->id }}" href="#">{{ $node->title }} <i class="fa-solid fa-chevron-down"></i></a>
                    <div class="md-sub" id="mdNav{{ $node->id }}" hidden>
                        @foreach($links as $link)
                            <a href="{{ $link->href() }}" @if($link->target) target="{{ $link->target }}" rel="noopener" @endif>{{ $link->title }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="md-foot">
        @auth
            <a href="{{ auth()->user()->home() }}" class="btn btn-brand btn-block">{{ ___('frontend.go_to_dashboard') }}</a>
        @else
            <a href="{{ route('loginForm') }}" class="btn btn-outline-brand btn-block">{{ ___('frontend.login') }}</a>
            <a href="{{ route('registerForm') }}" class="btn btn-brand btn-block">{{ ___('frontend.register') }}</a>
        @endauth
        @if($siteTel)
            <a href="tel:{{ $siteTel }}" class="btn btn-soft btn-block"><i class="fa-solid fa-phone"></i> {{ ___('frontend.call_us') }}</a>
        @endif
    </div>
</aside>

