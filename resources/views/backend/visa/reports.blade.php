@extends('backend.partials.master')
@section('title') {{ ___('label.visa') }} {{ ___('menus.reports') }} @endsection
@section('maincontent')
@php
    // Percentages are taken over decided cases only — counting in-progress
    // applications as failures understates the real approval rate.
    $total    = $byStatus->sum();
    $approved = (int) ($byStatus['Approved'] ?? 0);
    $rejected = (int) ($byStatus['Rejected'] ?? 0);
    $decided  = $approved + $rejected;
    $rate     = $decided ? round($approved / $decided * 100) : 0;
@endphp
<x-page :title="___('label.visa') . ' ' . ___('menus.reports')" :breadcrumb="[___('label.visa'), ___('menus.reports')]">
    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.approval_rate') }}</div>
            <h3 class="mb-0 text-success">{{ $rate }}%</h3>
            <small class="text-muted">{{ $approved }} / {{ $decided }} {{ ___('label.decided') }}</small>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.applications') }}</div>
            <h3 class="mb-0">{{ number_format($total) }}</h3>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.service_revenue') }}</div>
            <h3 class="mb-0">{{ currency_symbol() }}{{ number_format($earned) }}</h3>
            <small class="text-muted">{{ ___('label.of') }} {{ currency_symbol() }}{{ number_format($collected) }} {{ ___('label.collected') }}</small>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.rejections') }}</div>
            <h3 class="mb-0 text-danger">{{ number_format($rejected) }}</h3>
        </div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.revenue_by_service') }}</h4></div>
                <div class="tv-card-body">
                    <div class="table-responsive">
                        <table class="table tv-table">
                            <thead class="bg">
                                <tr>
                                    <th>{{ ___('label.country') }}</th>
                                    <th>{{ ___('label.visa_type') }}</th>
                                    <th class="text-right">{{ ___('label.applications') }}</th>
                                    <th class="text-right">{{ ___('label.govt_fee') }}</th>
                                    <th class="text-right">{{ ___('label.service_revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byService as $row)
                                <tr>
                                    <td><b>{{ $row->country }}</b></td>
                                    <td>{{ $row->visa_type }}</td>
                                    <td class="text-right">{{ number_format($row->total) }}</td>
                                    <td class="text-right text-muted">{{ currency_symbol() }}{{ number_format($row->govt_total) }}</td>
                                    <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($row->service_total) }}</b></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        {{ ___('alert.no_data_available') }}<br>
                                        <small>{{ ___('label.link_applications_to_services_hint') }}</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.by_country') }}</h4></div>
                <div class="tv-card-body"><div id="vrDonut"></div></div>
            </div>
        </div>
    </div>
</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#vrDonut",
      "labels": {!! $byCountry->keys()->toJson() !!},
      "series": {!! $byCountry->values()->toJson() !!}
    }
  ]
}
</script>
@endpush
