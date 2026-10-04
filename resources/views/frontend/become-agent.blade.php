@extends('frontend.layouts.master')
@section('title', ($page?->title ?: ___('frontend.become_agent')) . ' - ' . (settings('name') ?: 'FLOW'))
@section('meta', $page?->meta_description ?: ___('frontend.become_agent_meta_description'))
@section('page', 'become-agent')

@section('content')
@include('frontend.components.page-hero', [
    'title' => $page?->title ?: ___('frontend.become_agent_hero_title'),
    'subtitle' => $page?->hero_subtitle ?: str_replace(':name', settings('name') ?: 'FLOW', ___('frontend.become_agent_hero_subtitle')),
    'crumbs' => [($page?->breadcrumb_label ?: ___('frontend.become_agent')) => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-5">
            {{-- Benefits --}}
            <div class="col-lg-6">
                <span class="eyebrow"><i class="fa-solid {{ $page?->section_icon ?: 'fa-handshake' }}"></i> {{ $page?->section_eyebrow ?: ___('frontend.agent_program') }}</span>
                <h2 class="mt-2 mb-4">{{ $page?->section_heading ?: ___('frontend.why_partner_with_us') }}</h2>
                <div class="d-flex flex-column gap-3">
                    @foreach($benefits as $b)
                    <div class="feature-item"><div class="fi-icon"><i class="fa-solid {{ $b->icon ?: 'fa-circle-check' }}"></i></div><div><h5 class="mb-0 text-16">{{ $b->title }}</h5><span class="text-muted-2 text-14">{{ $b->body }}</span></div></div>
                    @endforeach
                </div>
            </div>

            {{-- Application form (creates a CRM Lead) --}}
            <div class="col-lg-6">
                <div class="enquiry-card">
                    <h4 class="mb-1">{{ $page?->form_title ?: ___('frontend.apply_to_become_agent') }}</h4>
                    <p class="text-muted-2 text-14 mb-4">{{ $page?->form_intro ?: ___('frontend.agent_form_intro') }}</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('front.become.agent.store') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ $page?->form_name_label ?: ___('frontend.full_name_company') }} *</label>
                            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ $page?->form_name_placeholder ?: ___('frontend.your_name_or_agency') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $page?->form_phone_label ?: ___('frontend.phone') }} *</label>
                                <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="{{ $page?->form_phone_placeholder ?: ___('frontend.phone_placeholder') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $page?->form_email_label ?: ___('frontend.email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ $page?->form_email_placeholder ?: ___('frontend.email_example') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">{{ $page?->form_notes_label ?: ___('frontend.tell_us_about_business') }}</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="{{ $page?->form_notes_placeholder ?: ___('frontend.agent_notes_placeholder') }}">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-brand btn-lg btn-block">{{ $page?->form_submit_button ?: ___('frontend.submit_application') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

