@extends('backend.partials.master')
@section('title') Visa Dashboard @endsection
@section('maincontent')
@php
$kpis = [
    ['Total Applications', number_format($total), '#4f46e5', 'fa-passport'],
    ['Approved', number_format($approved), '#10b981', 'fa-circle-check'],
    ['Processing', number_format($processing), '#f59e0b', 'fa-hourglass-half'],
    ['Rejected', number_format($rejected), '#ef4444', 'fa-circle-xmark'],
];
@endphp
<x-page title="Visa Dashboard" :breadcrumb="['Visa','Dashboard']">
    <div class="row">
        @foreach($kpis as $k)
        <div class="col-xl-3 col-md-6 mb-4"><div class="tv-card h-100"><div class="tv-card-body d-flex justify-content-between align-items-center">
            <div><div class="text-muted tv-text-xs">{{ $k[0] }}</div><h3 class="mb-0 tv-fw-700">{{ $k[1] }}</h3></div>
            <div class="tv-icon-tile-42" style="background:{{ $k[2] }}"><i class="fa-solid {{ $k[3] }}"></i></div>
        </div></div></div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-xl-8 mb-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Applications by Country</h4></div><div class="tv-card-body"><div id="visaChart"></div></div></div></div>
        <div class="col-xl-4 mb-4"><div class="tv-card h-100"><div class="tv-card-head"><h4 class="title-site mb-0">Status Split</h4></div><div class="tv-card-body"><div id="visaDonut"></div></div></div></div>
    </div>
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-head"><h4 class="title-site mb-0">Recent Applications</h4></div>
        <div class="tv-card-body"><div class="table-responsive"><table class="table table-responsive-sm">
            <thead class="bg"><tr><th>ID</th><th>Applicant</th><th>Country</th><th>Type</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recent as $r)
                @php $tone = $r->status === 'Approved' ? 'success' : ($r->status === 'Rejected' ? 'danger' : 'warning'); @endphp
                <tr>
                    <td><b>{{ $r->application_no }}</b></td>
                    <td>{{ $r->applicant_name }}</td>
                    <td>{{ $r->country }}</td>
                    <td>{{ $r->visa_type }}</td>
                    <td><span class="bullet-badge bullet-badge-{{ $tone }}">{{ $r->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">{{ ___('alert.no_data_available') }}</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
    </div></div></div>
</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#visaChart",
      "series": [{ "name": "Applications", "data": @json($byCountry['series']) }],
      "categories": @json($byCountry['labels'])
    },
    {
      "target": "#visaDonut",
      "labels": @json($byStatus['labels']),
      "series": @json($byStatus['series'])
    }
  ]
}
</script>
@endpush
