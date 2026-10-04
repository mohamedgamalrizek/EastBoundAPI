@extends('frontend.layouts.master')
@section('title', str_replace(':job', $job->title, ___('frontend.apply_for')) . ' - ' . (settings('name') ?: 'FLOW'))
@section('meta', str_replace(':job', $job->title, ___('frontend.submit_application_for')))
@section('page', 'career')

@section('content')
@include('frontend.components.page-hero', [
    'title' => str_replace(':job', $job->title, ___('frontend.apply_for')),
    'subtitle' => ___('frontend.career_apply_subtitle'),
    'crumbs' => [___('frontend.career_title') => route('front.career'), $job->title => null],
])

<section class="section section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="service-card">
                    <div class="sc-icon"><i class="fa-solid fa-briefcase"></i></div>
                    <h4 class="text-20">{{ $job->title }}</h4>
                    <div class="job-meta mb-3">
                        @if($job->department)<span class="badge-tv">{{ $job->department }}</span>@endif
                        @if($job->location)<span class="badge-tv"><i class="fa-solid fa-location-dot"></i> {{ $job->location }}</span>@endif
                        <span class="badge-tv is-success">{{ $job->employment_type }}</span>
                    </div>
                    @if($job->description)
                        <p class="text-muted-2">{{ $job->description }}</p>
                    @endif
                    @if($job->closing_date)
                        <p class="text-muted-2 mb-0"><i class="fa-regular fa-calendar"></i> {{ ___('frontend.apply_by_prefix') }} {{ dateFormat($job->closing_date) }}</p>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card p-4 lg:p-12">
                    <h3 class="mb-1">{{ ___('frontend.application_details') }}</h3>
                    <p class="text-muted-2 mb-4">{{ ___('frontend.application_details_copy') }}</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('front.career.apply.store', $job) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ ___('frontend.full_name') }} *</label>
                                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ ___('frontend.your_name_placeholder') }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ ___('frontend.email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ ___('frontend.email_example') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ ___('frontend.phone') }}</label>
                                <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="{{ ___('frontend.phone_placeholder') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ ___('frontend.linkedin_portfolio') }}</label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="{{ ___('frontend.url_placeholder_example') }}">
                                @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ ___('frontend.resume_cv') }}</label>
                                <input type="file" class="form-control @error('resume') is-invalid @enderror" name="resume" accept=".pdf,.doc,.docx">
                                <small class="text-muted">{{ ___('frontend.resume_help') }}</small>
                                @error('resume')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ ___('frontend.cover_letter') }}</label>
                                <textarea class="form-control @error('cover_letter') is-invalid @enderror" name="cover_letter" rows="5" placeholder="{{ ___('frontend.cover_letter_placeholder') }}">{{ old('cover_letter') }}</textarea>
                                @error('cover_letter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button class="btn btn-brand btn-lg"><i class="fa-solid fa-paper-plane"></i> {{ ___('frontend.submit_application') }}</button>
                                <a href="{{ route('front.career') }}" class="btn btn-outline-brand btn-lg">{{ ___('frontend.back_to_careers') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

