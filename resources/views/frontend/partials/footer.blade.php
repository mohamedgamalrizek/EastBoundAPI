{{-- ============================================================
     Footer — link columns come from CMS → Manage Menus; brand, contact
     details from Settings → General and social links from Settings → Social links.
     ============================================================ --}}
@php
    $siteName = settings('name') ?: 'FLOW';
    $socials = \App\Models\SocialLink::active()->ordered()->get();
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 gx-lg-5">
            {{-- Brand / about --}}
            <div class="col-lg-4 col-md-6">
                <a class="footer-brand" href="{{ route('home') }}">
                    @php $footerLogo = settings('dark_theme_logo') ?: settings('light_theme_logo'); @endphp
                    @if($footerLogo)
                        <img src="{{ logo($footerLogo) }}" alt="{{ $siteName }}" class="footer-brand-img">
                    @else
                        <span class="brand-logo"><i class="fa-solid fa-paper-plane"></i></span>
                        {{ $siteName }}
                    @endif
                </a>
                <p class="f-about">{{ settings('site_tagline') ?: ___('frontend.footer_tagline') }}</p>
                @if($socials->isNotEmpty())
                <div class="f-social">
                    @foreach($socials as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener" aria-label="{{ $social->name }}">
                            <i class="fa-brands {{ $social->icon }}"></i>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Link columns — CMS → Manage Menus (position: footer). Each
                 top-level item is a column heading, its children the links. --}}
            @foreach($footerMenu as $column)
            <div class="col-lg-2 col-md-3 col-6">
                <div class="f-title">{{ $column->title }}</div>
                <ul class="f-links">
                    @foreach($column->children as $link)
                        <li>
                            <a href="{{ $link->href() }}" @if($link->target) target="{{ $link->target }}" rel="noopener" @endif>{{ $link->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endforeach

            {{-- Contact --}}
            <div class="col-lg-4 col-md-12">
                <div class="f-title">{{ ___('frontend.get_in_touch') }}</div>
                @if(settings('address'))
                    <div class="f-contact-item"><i class="fa-solid fa-location-dot"></i> {{ settings('address') }}</div>
                @endif
                @if(settings('phone'))
                    <div class="f-contact-item"><i class="fa-solid fa-phone"></i> <a href="tel:{{ preg_replace('/[^\d+]/', '', settings('phone')) }}">{{ settings('phone') }}</a></div>
                @endif
                @if(settings('email'))
                    <div class="f-contact-item"><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ settings('email') }}">{{ settings('email') }}</a></div>
                @endif
                @if(settings('whatsapp'))
                    <div class="f-contact-item"><i class="fa-brands fa-whatsapp"></i> <a href="https://wa.me/{{ preg_replace('/\D/', '', settings('whatsapp')) }}" target="_blank" rel="noopener">{{ settings('whatsapp') }}</a></div>
                @endif

                @if(settings('app_store_url') || settings('play_store_url'))
                <div class="d-flex gap-2 mt-3 f-apps">
                    @if(settings('app_store_url'))
                        <a href="{{ settings('app_store_url') }}" target="_blank" rel="noopener"><i class="fa-brands fa-apple"></i> <span>{{ ___('frontend.app_store') }}</span></a>
                    @endif
                    @if(settings('play_store_url'))
                        <a href="{{ settings('play_store_url') }}" target="_blank" rel="noopener"><i class="fa-brands fa-google-play"></i> <span>{{ ___('frontend.google_play') }}</span></a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="fb-inner">
                <span>&copy; {{ date('Y') }} {{ settings('copyright') ?: $siteName . '. ' . ___('frontend.all_rights_reserved') }}</span>
                @if($footerLegalMenu->isNotEmpty())
                <span class="fb-links">
                    @foreach($footerLegalMenu as $link)
                        <a href="{{ $link->href() }}" @if($link->target) target="{{ $link->target }}" rel="noopener" @endif>{{ $link->title }}</a>
                    @endforeach
                </span>
                @endif
            </div>
        </div>
    </div>
</footer>

