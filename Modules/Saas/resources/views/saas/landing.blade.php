<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4DA6FF">
    <title>{{ config('saas-landing.meta.title') }}</title>
    <meta name="description" content="{{ config('saas-landing.meta.description') }}">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('frontend/scss/saas-landing-pages/saas-landing-main.css') }}?v={{ filemtime(public_path('frontend/scss/saas-landing-pages/saas-landing-main.css')) }}" rel="stylesheet">
</head>

<body class="saas-landing" dir="ltr">
@php $c = config('saas-landing'); @endphp

<!-- NAV -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="brand navbar-brand" href="#top">{{ $c['brand'] }}<span>.</span></a>
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="saas-nav-toggle" aria-hidden="true"><span></span></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="nav">
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">How it works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item ms-lg-2 d-flex align-items-center">
                    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark mode" aria-pressed="false" title="Switch to dark mode">
                        <i class="fa-solid fa-moon" aria-hidden="true"></i>
                    </button>
                </li>
                <li class="nav-item ms-lg-2"><a class="nav-link" href="{{ url('login') }}">Sign in</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-blue btn-sm px-3" href="{{ route('saas.signup') }}">Start free <i class="fa-solid fa-arrow-right"></i></a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="hero" id="top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="pill mb-3">{{ $c['hero']['pill'] }}</span>
                <h1 class="mb-3">{!! $c['hero']['headline'] !!}</h1>
                <p class="lead mb-4">{{ $c['hero']['lead'] }}</p>
                <div class="hero-actions d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('saas.signup') }}" class="btn btn-blue btn-lg px-4 mb-2">{{ $c['hero']['primary_cta'] }}</a>
                    <a href="#pricing" class="btn btn-ghost btn-lg px-4 mb-2">{{ $c['hero']['secondary_cta'] }}</a>
                </div>
                <p class="hero-benefits mt-3 mb-0">{!! $c['hero']['benefits'] !!}</p>
            </div>
            <div class="col-lg-6">
                <div class="mock">
                    <div class="mock-bar"><i class="mock-dot--red"></i><i class="mock-dot--yellow"></i><i class="mock-dot--green"></i></div>
                    <div class="mock-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="text-dark">Dashboard</strong>
                            <span class="badge badge-pill live-badge">● Live</span>
                        </div>
                        <div class="row no-gutters mx-n1">
                            <div class="col-4 px-1 mb-2"><div class="mini-stat"><div class="v">৳8.4L</div><div class="l">Revenue</div></div></div>
                            <div class="col-4 px-1 mb-2"><div class="mini-stat"><div class="v">312</div><div class="l">Bookings</div></div></div>
                            <div class="col-4 px-1 mb-2"><div class="mini-stat"><div class="v">47</div><div class="l">Leads</div></div></div>
                        </div>
                        <div class="mt-2 mb-1 d-flex justify-content-between"><small class="text-muted">Packages</small><small class="text-muted">82%</small></div>
                        <div class="bar bar--packages mb-2"></div>
                        <div class="mb-1 d-flex justify-content-between"><small class="text-muted">Visa</small><small class="text-muted">64%</small></div>
                        <div class="bar bar--visa mb-2"></div>
                        <div class="mb-1 d-flex justify-content-between"><small class="text-muted">Hajj</small><small class="text-muted">45%</small></div>
                        <div class="bar bar--hajj"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="stats py-4">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 py-2"><div class="n">{{ max($tenants, 1) }}+</div><div class="l">Agencies onboard</div></div>
            @foreach($c['stats'] as $stat)
                <div class="col-6 col-md-3 py-2"><div class="n">{{ $stat['value'] }}</div><div class="l">{{ $stat['label'] }}</div></div>
            @endforeach
        </div>
    </div>
</div>

<!-- FEATURES -->
<section id="features">
    <div class="container">
        <div class="sec-head text-center mb-5">
            <h2 class="mb-2">{{ $c['features_head']['title'] }}</h2>
            <p>{{ $c['features_head']['text'] }}</p>
        </div>
        <div class="row">
            @foreach($c['features'] as $f)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="feature">
                        <div class="ic">{{ $f['icon'] }}</div>
                        <h5>{{ $f['title'] }}</h5>
                        <p>{{ $f['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="how">
    <div class="container">
        <div class="sec-head text-center mb-5">
            <h2 class="mb-2">{{ $c['how_head']['title'] }}</h2>
            <p>{{ $c['how_head']['text'] }}</p>
        </div>
        <div class="row">
            @foreach($c['steps'] as $i => $step)
                <div class="col-md-4 mb-4"><div class="step"><div class="num">{{ $i + 1 }}</div><h6>{{ $step['title'] }}</h6><p>{!! str_replace(':domain', e($domainBase), $step['text']) !!}</p></div></div>
            @endforeach
        </div>
        <div class="text-center mt-3"><a href="{{ route('saas.signup') }}" class="btn btn-blue btn-lg px-4">Create my workspace <i class="fa-solid fa-arrow-right"></i></a></div>
    </div>
</section>

<!-- PRICING -->
<section id="pricing" class="pricing">
    <div class="container">
        <div class="sec-head text-center mb-5">
            <h2 class="mb-2">{{ $c['pricing_head']['title'] }}</h2>
            <p>{{ $c['pricing_head']['text'] }}</p>
        </div>
        <div class="row justify-content-center">
            @forelse($plans as $plan)
                @php $popular = $plans->count() >= 3 ? $loop->index === 1 : $loop->last; @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="plan {{ $popular ? 'popular' : '' }}">
                        @if($popular)<span class="tag">MOST POPULAR</span>@endif
                        <div class="pname mb-1">{{ $plan->name }}</div>
                        <div class="price mb-2">৳{{ number_format($plan->price) }}<small>/{{ $plan->billing_cycle }}</small></div>
                        <div class="plan-meta mb-2">{{ $plan->max_users ? $plan->max_users.' team members' : 'Unlimited team members' }}</div>
                        <ul>
                            @forelse(collect($plan->features)->take(6) as $feat)
                                <li>{{ $feat }}</li>
                            @empty
                                <li>All core travel-agency modules</li>
                                <li>Your own private workspace</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('saas.signup') }}" class="btn justify-content-center {{ $popular ? 'btn-blue' : 'btn-ghost' }} btn-block">Get started</a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">Plans are being set up. Please check back shortly.</div>
            @endforelse
        </div>
    </div>
</section>

<!-- PAYMENT GATEWAYS -->
<section class="gw text-center py-5">
    <div class="container">
        <p class="gateway-label mb-3">{{ $c['gateways_label'] }}</p>
        <div>
            @foreach($c['gateways'] as $g)
                <span class="chip">{{ $g }}</span>
            @endforeach
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials">
    <div class="container">
        <div class="sec-head text-center mb-5">
            <h2 class="mb-2">{{ $c['testimonials_head'] }}</h2>
        </div>
        <div class="row">
            @foreach($c['testimonials'] as $q)
                <div class="col-md-4 mb-4">
                    <div class="quote">
                        <div class="stars mb-2">★★★★★</div>
                        <p>“{{ $q['quote'] }}”</p>
                        <div class="who">{{ $q['name'] }}</div>
                        <div class="role">{{ $q['role'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="faq">
    <div class="container">
        <div class="sec-head text-center mb-5"><h2 class="mb-2">{{ $c['faq_head'] }}</h2></div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="faqAcc">
                    @foreach($c['faqs'] as $i => $f)
                        <div class="card">
                            <div class="card-header bg-white p-0 border-0">
                                <button class="btn btn-link collapsed justify-content-between" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                                    <span class="flex-grow-1">{{ $f['q'] }}</span>
                                    <span class="text-primary">+</span>
                                </button>
                            </div>
                            <div id="faq{{ $i }}" class="collapse" data-bs-parent="#faqAcc">
                                <div class="card-body pt-0">{{ $f['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="pt-0">
    <div class="container">
        <div class="cta text-center">
            <h2 class="mb-2">{{ $c['cta']['title'] }}</h2>
            <p class="cta-copy mb-4">{{ $c['cta']['text'] }}</p>
            <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                <a href="{{ route('saas.signup') }}" class="btn btn-white btn-lg px-4 me-2 mb-2">{{ $c['hero']['primary_cta'] }} <i class="fa-solid fa-arrow-right"></i></a>
                <a href="{{ url('login') }}" class="btn btn-outline-light btn-lg px-4 mb-2">Sign in</a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="saas-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <span class="brand">{{ $c['brand'] }}<span>.</span></span>
                <p class="footer-tagline mb-0 mt-2">{{ $c['tagline'] }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    <a href="#features" class="me-3">Features</a>
                    <a href="#pricing" class="me-3">Pricing</a>
                    <a href="#faq" class="me-3">FAQ</a>
                    <a href="{{ route('saas.signup') }}">Get started</a>
                </div>
                <p class="footer-copyright mt-3 mb-0">© {{ now()->year }} {{ $c['brand'] }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('frontend/js/app.js') }}"></script>
</body>
</html>
