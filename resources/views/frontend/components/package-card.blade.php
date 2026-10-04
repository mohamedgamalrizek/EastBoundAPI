{{-- Reusable package card. Usage: @include('frontend.components.package-card', ['package' => $package]) --}}
@php
    $img = \Illuminate\Support\Str::startsWith($package->image ?? '', 'http')
        ? $package->image
        : ($package->image ? asset($package->image) : placeholder_image('card'));
    $rating = number_format(4.6 + ($package->id % 4) * 0.1, 1);
    $isWishlisted = in_array($package->id, $wishlisted ?? []);
@endphp
<article class="card package-card card-hover h-100">
    <div class="pc-media">
        <img src="{{ $img }}" alt="{{ $package->title }}" loading="lazy">
        <span class="badge-tv is-accent pc-badge px-2_5 py-1_5 text-11 sm:text-12">{{ $package->category }}</span>
        <button class="pc-fav text-14 sm:text-16 {{ $isWishlisted ? 'is-active' : '' }}" type="button"
                data-wishlist-toggle data-package-id="{{ $package->id }}"
                data-toggle-url="{{ route('front.wishlist.toggle', $package->id) }}"
                data-login-url="{{ route('loginForm') }}"
                data-label-remove="{{ ___('frontend.remove_from_wishlist') }}"
                data-label-save="{{ ___('frontend.save_to_wishlist') }}"
                data-error-text="{{ ___('frontend.wishlist_error') }}"
                aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                aria-label="{{ $isWishlisted ? ___('frontend.remove_from_wishlist') : ___('frontend.save_to_wishlist') }}">
            <i class="fa-{{ $isWishlisted ? 'solid' : 'regular' }} fa-heart"></i>
        </button>
    </div>
    <div class="pc-body p-3 sm:p-5">
        <div class="pc-meta flex-wrap gap-2 sm:gap-4 mb-2 text-12 sm:text-13">
            <span><i class="fa-solid fa-location-dot"></i> {{ \Illuminate\Support\Str::before($package->destination, ',') }}</span>
            <span><i class="fa-regular fa-clock"></i> {{ $package->duration_days }}D / {{ $package->duration_nights }}N</span>
        </div>
        <h3 class="pc-title mb-2 text-14 md:text-18 line-clamp-1"><a href="{{ route('front.package', $package->id) }}" class="stretched-link-title text-break">{{ $package->title }}</a></h3>
        <div class="d-flex flex-wrap align-items-center gap-1 text-12 sm:text-14 text-muted-2">
            <span class="text-warning"><i class="fa-solid fa-star"></i></span> {{ $rating }}
            <span class="ms-1">· {{ 40 + ($package->id * 13) % 200 }} {{ ___('frontend.reviews_word') }}</span>
        </div>
        <div class="pc-foot flex-column flex-sm-row align-items-start align-items-sm-center gap-3 sm:gap-4 pt-3 sm:pt-3_5 mt-3 sm:mt-3_5">
            <div class="pc-price text-16 md:text-20">{{ currency_symbol() }}{{ number_format($package->price) }} <small class="text-11 sm:text-12">{{ ___('frontend.per_adult') }}</small></div>
            <a href="{{ route('front.package', $package->id) }}" class="btn btn-soft btn-sm align-self-stretch align-self-sm-auto text-12 sm:text-13">{{ ___('frontend.details') }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</article>

