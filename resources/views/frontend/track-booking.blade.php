@extends('frontend.layouts.master')
@section('title', ___('frontend.track_booking_hero_title') . ' — FLOW')
@section('meta', ___('frontend.track_booking_meta_description'))
@section('page', 'track-booking')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.track_booking_hero_title'),
    'subtitle' => ___('frontend.track_booking_subtitle'),
    'crumbs' => [___('frontend.track_booking') => null],
])

<section class="section section-space">
    <div class="container tracking-container">
        <div class="enquiry-card mb-4">
            <form action="{{ route('front.track.booking') }}" method="get" class="row g-2 align-items-end">
                <div class="col-md-9 field">
                    <label class="field-label">{{ ___('frontend.booking_phone_label') }}</label>
                    <div class="input-icon"><i class="fa-solid fa-phone"></i><input class="form-control" name="phone" value="{{ request('phone') }}" placeholder="+880 17…" required></div>
                </div>
                <div class="col-md-3"><button class="btn btn-brand btn-block"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.track') }}</button></div>
            </form>
        </div>

        @if($searched)
            @php
                $statusLabels = [
                    'pending'   => ___('frontend.booking_status_pending'),
                    'confirmed' => ___('frontend.booking_status_confirmed'),
                    'cancelled' => ___('frontend.booking_status_cancelled'),
                    'completed' => ___('frontend.booking_status_completed'),
                ];
            @endphp
            @forelse($results as $b)
            <div class="card p-4 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">{{ optional($b->package)->title ?? ___('frontend.custom_booking') }}</h5>
                        <div class="text-muted-2 text-14">{{ ___('frontend.ref_number_prefix') }}TRV-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }} · {{ $b->customer_name }}</div>
                    </div>
                    @php $map=['pending'=>'is-warning','confirmed'=>'is-success','cancelled'=>'is-danger','completed'=>'is-accent']; @endphp
                    <span class="badge-tv {{ $map[$b->status] ?? '' }}">{{ $statusLabels[$b->status] ?? ucfirst($b->status) }}</span>
                </div>
                <ul class="track-timeline">
                    <li class="done"><span class="dot"></span><div class="t-title">{{ ___('frontend.booking_received') }}</div><div class="t-time">{{ $b->created_at->format('d M Y, h:i A') }}</div></li>
                    <li class="{{ in_array($b->status,['confirmed','completed']) ? 'done' : ($b->status=='cancelled'?'':'active') }}"><span class="dot"></span><div class="t-title">{{ ___('frontend.confirmation') }}</div><div class="t-time">{{ $b->status=='pending' ? ___('frontend.awaiting_confirmation') : ($statusLabels[$b->status] ?? ucfirst($b->status)) }}</div></li>
                    <li class="{{ $b->status=='completed' ? 'done' : '' }}"><span class="dot"></span><div class="t-title">{{ ___('frontend.trip_completed') }}</div><div class="t-time">{{ ___('frontend.travel_date') }}: {{ optional($b->travel_date)->format('d M Y') ?? '—' }}</div></li>
                </ul>
                <div class="d-flex justify-content-between border-top pt-3 mt-2">
                    <span class="text-muted-2">{{ ___('frontend.travelers') }}: {{ $b->travelers }}</span>
                    <span class="pc-price price-size-md">{{ currency_symbol() }}{{ number_format($b->amount) }}</span>
                </div>
            </div>
            @empty
            <div class="card p-5 text-center text-muted-2">
                <div class="mb-2 empty-state-icon-sm"><i class="fa-regular fa-face-frown"></i></div>
                {{ ___('frontend.no_bookings_found') }} <a href="{{ route('front.contact') }}">{{ ___('frontend.contact_support') }}</a>.
            </div>
            @endforelse
        @else
            <p class="text-center text-muted-2">{{ ___('frontend.booking_status_hint') }}</p>
        @endif
    </div>
</section>
@endsection

