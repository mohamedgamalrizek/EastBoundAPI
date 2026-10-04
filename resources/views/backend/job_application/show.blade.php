@extends('backend.partials.master')
@section('title') Job Application @endsection
@section('maincontent')
<x-page title="Job Application" :breadcrumb="['CRM','Job Applications','Details']">
    <x-slot:action>
        <a href="{{ route('crm.job-applications.index') }}" class="j-td-btn btn-red"><span>Back</span></a>
    </x-slot:action>

    <div class="row">
        <div class="col-lg-8">
            <div class="tv-card">
                <div class="tv-card-body">
                    <h4 class="title-site mb-3">{{ $application->jobOpening?->title ?: 'Deleted role' }}</h4>
                    @if($application->cover_letter)
                        <p class="tv-pre-wrap">{{ $application->cover_letter }}</p>
                    @else
                        <p class="text-muted mb-0">No cover letter provided.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="tv-card">
                <div class="tv-card-body">
                    <table class="table mb-0">
                        <tr><th>Name</th><td>{{ $application->name }}</td></tr>
                        <tr><th>Email</th><td>@if($application->email)<a href="mailto:{{ $application->email }}">{{ $application->email }}</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>Phone</th><td>@if($application->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $application->phone) }}">{{ $application->phone }}</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>LinkedIn</th><td>@if($application->linkedin_url)<a href="{{ $application->linkedin_url }}" target="_blank" rel="noopener">Open profile</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>Resume</th><td>@if($application->resume_path)<a href="{{ route('crm.job-applications.resume', $application->id) }}">Download CV</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                        <tr><th>Status</th><td>{!! $application->statusBadge() !!}</td></tr>
                        <tr><th>Submitted</th><td>{{ $application->created_at?->format('d M Y, h:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
