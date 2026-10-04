@extends('backend.partials.master')
@section('title') Flight Reports @endsection
@section('maincontent')
<x-page title="Flight Reports" :breadcrumb="['Flight','Reports']">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Bookings</div><h3 class="mb-0 tv-fw-700">{{ $totalCount }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Tickets Issued</div><h3 class="mb-0 tv-fw-700">{{ $ticketed }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Total Fare</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalFare) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">Refunds</div><h3 class="mb-0 text-danger tv-fw-700">{{ $byStatus['Refunded'] ?? 0 }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Bookings by Status</h4></div>
                <div class="tv-card-body">
                    <x-data-table :headers="['Status','Bookings']" :card="false">
                        @foreach($byStatus as $status => $total)
                            @php $c = in_array($status, ['Confirmed','Reissued']) ? 'success' : ($status === 'Pending' ? 'warning' : 'danger'); @endphp
                            <tr>
                                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $status }}</span></td>
                                <td><b>{{ $total }}</b></td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Bookings by Airline</h4></div>
                <div class="tv-card-body"><div id="flRep"></div></div>
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
      "target": "#flRep",
      "series": [{ "name": "Bookings", "data": {!! json_encode(array_values($byAirline->toArray())) !!} }],
      "categories": {!! json_encode(array_keys($byAirline->toArray())) !!}
    }
  ]
}
</script>
@endpush
