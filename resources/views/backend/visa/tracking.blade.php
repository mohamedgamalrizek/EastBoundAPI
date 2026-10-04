@extends('backend.partials.master')
@section('title') {{ ___('label.visa') }} {{ ___('label.status_tracking') }} @endsection
@section('maincontent')
<x-page :title="___('label.status_tracking')" :breadcrumb="[___('label.visa'), ___('label.status_tracking')]">

    @php
        $open = $applications->whereNotIn('status', ['Approved', 'Rejected']);
    @endphp

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.in_progress') }}</div>
            <h3 class="mb-0 text-warning">{{ $open->count() }}</h3>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.awaiting_documents') }}</div>
            <h3 class="mb-0">{{ $open->where('documents_status', 'Pending')->count() }}</h3>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.at_embassy') }}</div>
            <h3 class="mb-0 text-info">{{ $open->where('status', 'In Review')->count() }}</h3>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.decided') }}</div>
            <h3 class="mb-0 text-success">{{ $applications->whereIn('status', ['Approved', 'Rejected'])->count() }}</h3>
        </div></div></div>
    </div>

    <div class="row">
        @forelse($applications as $application)
        @php $tone = $application->statusTone(); @endphp
        <div class="col-xl-6">
            <div class="tv-card js-visa-card">
                <div class="tv-card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <a href="{{ route('visa.application.details', $application->id) }}"><b>{{ $application->application_no }}</b></a>
                            <span class="bullet-badge bullet-badge-{{ $tone }} js-visa-badge">{{ $application->status }}</span>
                            <div class="text-muted tv-text-sm">
                                {{ $application->applicant_name }} · {{ $application->country }} · {{ $application->visa_type }}
                            </div>
                        </div>
                        <h5 class="mb-0"><span class="js-visa-percent">{{ $application->progress() }}</span>%</h5>
                    </div>

                    <div class="progress mt-2 tv-progress-slim">
                        <div class="progress-bar bg-{{ $tone }} js-visa-bar" style="width:{{ $application->progress() }}%"></div>
                    </div>

                    {{-- Shown only while the dropdowns hold something that has
                         not been saved, so a moved bar is never mistaken for a
                         stored decision. --}}
                    <small class="text-warning js-visa-unsaved d-none">
                        <i class="fa fa-circle-exclamation"></i> {{ ___('label.press_update_to_save') }}
                    </small>

                    <div class="d-flex flex-wrap gap-3 mt-2 text-muted tv-text-xs">
                        <span><i class="fa fa-file-lines"></i> {{ ___('label.documents') }}: {{ $application->documents_status }}</span>
                        @if($application->applied_date)
                            <span><i class="fa fa-paper-plane"></i> {{ $application->applied_date->format('d M Y') }}</span>
                        @endif
                        @if($application->appointment_date)
                            <span><i class="fa fa-calendar-check"></i> {{ $application->appointment_date->format('d M Y') }}</span>
                        @endif
                    </div>

                    {{-- A decided case is final; showing the form would invite a
                         move the repository is going to reject anyway. --}}
                    @if(hasPermission('visa_update') && ! in_array($application->status, ['Approved', 'Rejected'], true))
                    <form action="{{ route('visa.advance', $application->id) }}" method="post" class="form-row align-items-end mt-3">
                        @csrf @method('PUT')

                        <div class="form-group col-sm-4 mb-2">
                            <label class="label-style-1">{{ ___('label.documents') }}</label>
                            <select name="documents_status" class="form-control input-style-1 js-visa-documents">
                                @foreach($documentStatuses as $ds)
                                    <option value="{{ $ds }}" @selected($application->documents_status === $ds)>{{ $ds }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-sm-4 mb-2">
                            <label class="label-style-1">{{ ___('label.status') }}</label>
                            <select name="status" class="form-control input-style-1 js-visa-status">
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" @selected($application->status === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Captured with the decision so Expiry Management has a
                             date to work from the moment a visa is approved. --}}
                        <div class="form-group col-sm-4 mb-2">
                            <label class="label-style-1">{{ ___('label.expiry_date') }}</label>
                            <input type="date" name="expiry_date" class="form-control input-style-1"
                                   value="{{ $application->expiry_date?->format('Y-m-d') }}">
                        </div>

                        <div class="form-group col-12 mb-0">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-check"></i> {{ ___('label.update') }}
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="tv-card"><div class="tv-card-body text-center text-muted py-5">
                {{ ___('alert.no_data_available') }}
            </div></div>
        </div>
        @endforelse
    </div>
</x-page>
@endsection

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/visa-tracking.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/visa-tracking.js')) }}"></script>
@endpush
