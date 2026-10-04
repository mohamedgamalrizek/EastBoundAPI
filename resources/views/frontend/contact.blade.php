@extends('frontend.layouts.master')
@section('title', ($page?->title ?: ___('frontend.contact_us_title')) . ' - ' . (settings('name') ?: 'FLOW'))
@section('meta', $page?->meta_description ?: ___('frontend.contact_meta_description'))
@section('page', 'contact')

@section('content')
@include('frontend.components.page-hero', [
    'title' => $page?->title ?: ___('frontend.contact_us_title'),
    'subtitle' => $page?->hero_subtitle ?: ___('frontend.contact_hero_subtitle'),
    'crumbs' => [($page?->breadcrumb_label ?: ___('frontend.contact_us_title')) => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-3">
                    @if(settings('address'))
                        <div class="service-card">
                            <div class="sc-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <h4 class="text-20">{{ $page?->contact_address_label ?: ___('frontend.visit_us') }}</h4>
                            <p>{{ settings('address') }}</p>
                        </div>
                    @endif
                    @if(settings('phone') || settings('phone_secondary'))
                        <div class="service-card">
                            <div class="sc-icon"><i class="fa-solid fa-phone"></i></div>
                            <h4 class="text-20">{{ $page?->contact_phone_label ?: ___('frontend.call_us') }}</h4>
                            <p>
                                @foreach(array_filter([settings('phone'), settings('phone_secondary')]) as $number)
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $number) }}">{{ $number }}</a>@if(! $loop->last)<br>@endif
                                @endforeach
                            </p>
                        </div>
                    @endif
                    @if(settings('email'))
                        <div class="service-card">
                            <div class="sc-icon"><i class="fa-solid fa-envelope"></i></div>
                            <h4 class="text-20">{{ $page?->contact_email_label ?: ___('frontend.email_us') }}</h4>
                            <p><a href="mailto:{{ settings('email') }}">{{ settings('email') }}</a></p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card p-4 lg:p-12">
                    <h3 class="mb-1">{{ $page?->form_title ?: ___('frontend.send_us_a_message') }}</h3>
                    <p class="text-muted-2 mb-4">{{ $page?->form_intro ?: ___('frontend.contact_form_intro') }}</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('danger'))
                        <div class="alert alert-danger">{{ session('danger') }}</div>
                    @endif

                    @php
                        $subjects = collect(preg_split('/\r\n|\r|\n/', (string) ($page?->subject_options ?: "General enquiry\nTour packages\nVisa services")))
                            ->map(fn ($subject) => trim($subject))
                            ->filter()
                            ->values()
                            ->all();
                        $selectedSubject = old('subject_choice', in_array(request('subject'), $subjects, true) ? request('subject') : ($subjects[0] ?? 'General enquiry'));
                    @endphp

                    <form action="{{ route('front.contact.store') }}" method="post">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $page?->form_name_label ?: ___('frontend.full_name') }} *</label>
                                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ $page?->form_name_placeholder ?: ___('frontend.your_name_placeholder') }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $page?->form_email_label ?: ___('frontend.email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ $page?->form_email_placeholder ?: ___('frontend.email_example') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $page?->form_phone_label ?: ___('frontend.phone') }}</label>
                                <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="{{ $page?->form_phone_placeholder ?: ___('frontend.phone_placeholder') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $page?->form_notes_label ?: ___('label.subject') }}</label>
                                <select id="contactSubject" class="form-select select2 @error('subject') is-invalid @enderror" name="subject_choice">
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject }}" @selected($selectedSubject === $subject)>{{ $subject }}</option>
                                    @endforeach
                                    <option value="Other" @selected(old('subject_choice') === 'Other')>{{ ___('frontend.other') }}</option>
                                </select>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 d-none" id="customSubjectWrap">
                                <label class="form-label">{{ $page?->form_notes_placeholder ?: ___('frontend.custom_subject') }}</label>
                                <input class="form-control @error('subject_custom') is-invalid @enderror" name="subject_custom" value="{{ old('subject_custom', request('subject') && ! in_array(request('subject'), $subjects, true) ? request('subject') : '') }}" placeholder="{{ $page?->form_notes_placeholder ?: ___('frontend.type_your_subject') }}">
                                @error('subject_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ $page?->form_message_label ?: ___('label.message') }} *</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="5" placeholder="{{ $page?->form_message_placeholder ?: ___('frontend.how_can_we_help') }}">{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button class="btn btn-brand btn-lg"><i class="fa-solid fa-paper-plane"></i> {{ $page?->form_submit_button ?: ___('frontend.send_message') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('frontend/js/pages/contact.js') }}?v={{ filemtime(public_path('frontend/js/pages/contact.js')) }}"></script>
@endpush

