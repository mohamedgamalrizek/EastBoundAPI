@extends('backend.partials.master')
@section('title') Application Details @endsection
@section('maincontent')
@php
    $statusColor = $application->status === 'Approved' ? 'success' : ($application->status === 'Rejected' ? 'danger' : 'warning');
    $docColor    = $application->documents_status === 'Verified' ? 'success' : ($application->documents_status === 'Submitted' ? 'info' : 'warning');
@endphp
<x-page title="Application Details" :breadcrumb="['Visa','Applications','Details']">

    @if(hasPermission('visa_update'))
    <x-slot name="action">
        <a href="{{ route('visa.application.edit', $application->id) }}" class="j-td-btn"><i class="fa fa-edit"></i> <span>{{ ___('label.edit') }}</span></a>
    </x-slot>
    @endif

    <div class="row">
        <div class="col-lg-5 mb-4"><div class="tv-card"><div class="tv-card-body">
            <h4 class="mb-1">{{ $application->application_no }}</h4>
            <span class="bullet-badge bullet-badge-{{ $statusColor }}">{{ $application->status }}</span>
            <hr>
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><span class="text-muted">{{ ___('label.applicant_name') }}:</span> <b>{{ $application->applicant_name }}</b></li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.customer') }}:</span> {{ $application->customer->name ?? '—' }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.package') }}:</span> {{ $application->package->title ?? '—' }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.country') }}:</span> {{ $application->country }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.visa_type') }}:</span> {{ $application->visa_type }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.applied_date') }}:</span> {{ $application->applied_date?->format('d M Y') ?? '—' }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.appointment_date') }}:</span> {{ $application->appointment_date?->format('d M Y') ?? '—' }}</li>
                <li class="mb-2"><span class="text-muted">{{ ___('label.expiry_date') }}:</span> {{ $application->expiry_date?->format('d M Y') ?? '—' }}</li>
                <li class="mb-0"><span class="text-muted">{{ ___('label.documents_status') }}:</span> <span class="bullet-badge bullet-badge-{{ $docColor }}">{{ $application->documents_status }}</span></li>
            </ul>
        </div></div></div>

        <div class="col-lg-7 mb-4">
            <div class="tv-card"><div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.status') }}</h4></div><div class="tv-card-body">
                <table class="table table-responsive-sm mb-0">
                    <tbody>
                        <tr><td class="text-muted">{{ ___('label.documents_status') }}</td><td class="text-right"><span class="bullet-badge bullet-badge-{{ $docColor }}">{{ $application->documents_status }}</span></td></tr>
                        <tr><td class="text-muted">{{ ___('label.status') }}</td><td class="text-right"><span class="bullet-badge bullet-badge-{{ $statusColor }}">{{ $application->status }}</span></td></tr>
                        <tr><td class="text-muted">{{ ___('label.applied_date') }}</td><td class="text-right">{{ $application->applied_date?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">{{ ___('label.appointment_date') }}</td><td class="text-right">{{ $application->appointment_date?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">{{ ___('label.expiry_date') }}</td><td class="text-right">{{ $application->expiry_date?->format('d M Y') ?? '—' }}</td></tr>
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>

    @if($application->appointment_notes)
    <div class="row">
        <div class="col-12 mb-4"><div class="tv-card"><div class="tv-card-head"><h4 class="title-site mb-0">Applicant Notes</h4></div><div class="tv-card-body">
            {{-- What the applicant told us on the intake form (nationality, intended travel
                 date, contact details) — or, once an embassy appointment is booked, whatever
                 staff typed on the Book Appointment screen. Both share this one column. --}}
            <p class="mb-0 tv-pre-line">{{ $application->appointment_notes }}</p>
        </div></div></div>
    </div>
    @endif
</x-page>
@endsection
