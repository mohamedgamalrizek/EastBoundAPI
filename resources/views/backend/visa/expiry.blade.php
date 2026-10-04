@extends('backend.partials.master')
@section('title') {{ ___('label.visa') }} {{ ___('label.expiry_management') }} @endsection
@section('maincontent')
<x-page :title="___('label.expiry_management')" :breadcrumb="[___('label.visa'), ___('label.expiry_management')]">

    <div class="row">
        <div class="col-md-4"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.expiring_in') }} 15 {{ ___('label.days') }}</div>
            <h3 class="mb-0 text-danger">{{ $within15 }}</h3>
        </div></div></div>
        <div class="col-md-4"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.expiring_in') }} 16–30 {{ ___('label.days') }}</div>
            <h3 class="mb-0 text-warning">{{ $within30 }}</h3>
        </div></div></div>
        <div class="col-md-4"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.expiring_in') }} 31–90 {{ ___('label.days') }}</div>
            <h3 class="mb-0">{{ $within90 }}</h3>
        </div></div></div>
    </div>

    <div class="row"><div class="col-12"><div class="tv-card">
        <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.upcoming_expiries') }}</h4></div>
        <div class="tv-card-body"><div class="table-responsive">
            <table class="table table-responsive-sm">
                <thead class="bg"><tr>
                    <th>{{ ___('label.holder') }}</th>
                    <th>{{ ___('label.visa') }}</th>
                    <th>{{ ___('label.expiry_date') }}</th>
                    <th>{{ ___('label.days_left') }}</th>
                    <th>{{ ___('label.action') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($applications as $application)
                    <tr>
                        <td>
                            <b>{{ $application->applicant_name }}</b>
                            @if($application->customer)
                                <div class="text-muted tv-text-xs">{{ $application->customer->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $application->country }} · {{ $application->visa_type }}</td>
                        <td>{{ $application->expiry_date->format('d M Y') }}</td>
                        <td><span class="bullet-badge bullet-badge-{{ $application->expiryTone() }}">
                            {{ $application->daysToExpiry() }} {{ ___('label.days') }}
                        </span></td>
                        <td>
                            {{-- Renewals are the cheapest repeat sale, and they are
                                 lost by nobody making the call. --}}
                            @if(hasPermission('visa_update') && $application->customer)
                            <form action="{{ route('visa.expiry.notify', $application->id) }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-bell"></i> {{ ___('label.notify') }}
                                </button>
                            </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div></div></div>

    @if($expired->count())
    <div class="row"><div class="col-12"><div class="tv-card">
        <div class="tv-card-head">
            <h4 class="title-site mb-0">{{ ___('label.recently_expired') }}</h4>
            <span class="text-muted tv-text-sm">{{ ___('label.renewal_leads_hint') }}</span>
        </div>
        <div class="tv-card-body"><div class="table-responsive">
            <table class="table table-responsive-sm">
                <thead class="bg"><tr>
                    <th>{{ ___('label.holder') }}</th>
                    <th>{{ ___('label.visa') }}</th>
                    <th>{{ ___('label.expiry_date') }}</th>
                    <th>{{ ___('label.action') }}</th>
                </tr></thead>
                <tbody>
                    @foreach($expired as $application)
                    <tr>
                        <td>{{ $application->applicant_name }}</td>
                        <td>{{ $application->country }} · {{ $application->visa_type }}</td>
                        <td class="text-muted">{{ $application->expiry_date->format('d M Y') }}</td>
                        <td>
                            @if(hasPermission('visa_update') && $application->customer)
                            <form action="{{ route('visa.expiry.notify', $application->id) }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-bell"></i> {{ ___('label.notify') }}
                                </button>
                            </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div></div></div>
    @endif
</x-page>
@endsection
