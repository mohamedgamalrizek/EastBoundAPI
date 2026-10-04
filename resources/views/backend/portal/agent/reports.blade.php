@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_reports') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_reports') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.reports')]">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.total_sales') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalSales) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('permissions.bookings') }}</div><h3 class="mb-0 tv-fw-700">{{ $bookings }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.commission') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($commission) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('permissions.customers') }}</div><h3 class="mb-0 tv-fw-700">{{ $customers }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-data-table id="dt_booking_status" :headers="[___('label.booking_status'), ___('label.count')]">
                @foreach($byStatus as $status => $total)
                    <tr><td>{{ ucfirst($status) }}</td><td>{{ $total }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-md-6">
            <x-data-table id="dt_invoice_status" :headers="[___('label.invoice_status'), ___('label.count')]">
                @foreach($invoiceByStatus as $status => $total)
                    <tr><td>{{ ucfirst($status) }}</td><td>{{ $total }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

    <div class="tv-card">
        <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.bookings_by_status') }}</h4></div>
        <div class="tv-card-body"><div id="agRep"></div></div>
    </div>

</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#agRep",
      "series": [{ "name": "{{ ___('permissions.bookings') }}", "data": {!! json_encode(array_values($byStatus->toArray())) !!} }],
      "categories": {!! json_encode(array_map('ucfirst', array_keys($byStatus->toArray()))) !!}
    }
  ]
}
</script>
@endpush
