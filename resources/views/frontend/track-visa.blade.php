@extends('frontend.layouts.master')
@section('title', ___('frontend.track_visa_title') . ' — ' . (settings('name') ?: 'FLOW'))
@section('meta', ___('frontend.track_visa_meta_description'))
@section('page', 'track-visa')

@section('content')
@include('frontend.components.page-hero', [
    'title' => ___('frontend.track_visa_title'),
    'subtitle' => ___('frontend.track_visa_subtitle'),
    'crumbs' => [___('frontend.track_visa_crumb') => null],
])

<section class="section section-space">
    <div class="container tracking-container">
        <div class="enquiry-card mb-4 md:mb-6 p-4 md:p-6">
            <form action="{{ route('front.track.visa') }}" method="get" class="row g-2 align-items-end">
                <div class="col-md-9 field">
                    <label class="field-label">{{ ___('frontend.visa_application_reference') }}</label>
                    <div class="input-icon"><i class="fa-solid fa-passport"></i>
                        <input class="form-control" name="ref" value="{{ request('ref') }}" placeholder="{{ ___('frontend.visa_reference_example') }}" required>
                    </div>
                </div>
                <div class="col-md-3"><button class="btn btn-brand btn-block"><i class="fa-solid fa-magnifying-glass"></i> {{ ___('frontend.track') }}</button></div>
            </form>
        </div>

        @if($application)
            @php
                $badge = match ($application->status) {
                    'Approved' => 'is-success',
                    'Rejected' => 'is-danger',
                    default    => 'is-accent',
                };
            @endphp
            <div class="card p-4 md:p-6">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">{{ $application->country }} {{ $application->visa_type }} {{ ___('frontend.visa') }}</h5>
                        <div class="text-muted-2 text-14">{{ ___('frontend.ref_number_prefix') }}{{ $application->application_no }} · {{ $application->applicant_name }}</div>
                    </div>
                    <span class="badge-tv {{ $badge }}">{{ $application->status }}</span>
                </div>

                <ul class="track-timeline">
                    @foreach($timeline as $stage)
                    <li class="{{ $stage['state'] }}">
                        <span class="dot"></span>
                        <div class="t-title">{{ $stage['title'] }}</div>
                        <div class="t-time">{{ $stage['time'] }}</div>
                    </li>
                    @endforeach
                </ul>

                <div class="alert alert-info mt-2 mb-0 py-2 text-14">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    {{ ___('frontend.visa_question') }} <a href="{{ route('front.contact') }}">{{ ___('frontend.contact_visa_team') }}</a>.
                </div>
            </div>
        @elseif($searched)
            <div class="card p-4 md:p-6 text-center">
                <i class="fa-solid fa-magnifying-glass fa-2x text-muted-2 mb-3"></i>
                <h5 class="mb-1">{{ ___('frontend.no_application_found') }}</h5>
                <p class="text-muted-2 mb-3">
                    {!! str_replace(':ref', '<b>' . e(request('ref')) . '</b>', ___('frontend.application_not_found_message')) !!}
                </p>
                <div><a href="{{ route('front.contact') }}" class="btn btn-brand">{{ ___('frontend.ask_our_team') }}</a></div>
            </div>
        @else
            <div class="card p-4 md:p-6 text-center">
                <i class="fa-solid fa-passport fa-2x text-muted-2 mb-3"></i>
                <p class="text-muted-2 mb-0">{{ ___('frontend.enter_reference_status') }}</p>
            </div>
        @endif
    </div>
</section>
@endsection

