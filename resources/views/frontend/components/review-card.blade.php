{{-- A single CMS testimonial. Expects: $review (App\Models\Testimonial).
     Falls back to a generated initials avatar when no photo is uploaded. --}}
@php
    $rating = max(0, min(5, (int) $review->rating));
    $avatar = media_url($review->avatar, initials_avatar($review->name));
    $meta   = collect([$review->role, $review->city])->filter()->implode(' · ');
@endphp
<div class="review-card h-100">
    <div class="rc-stars">
        {!! str_repeat('<i class="fa-solid fa-star"></i>', $rating) . str_repeat('<i class="fa-regular fa-star"></i>', 5 - $rating) !!}
    </div>
    <p class="rc-text">“{{ $review->message }}”</p>
    <div class="rc-user">
        <img class="rc-avatar" src="{{ $avatar }}" alt="{{ $review->name }}" loading="lazy">
        <div>
            <p class="rc-name">{{ $review->name }}</p>
            @if($meta)<span class="rc-role">{{ $meta }}</span>@endif
        </div>
    </div>
</div>

