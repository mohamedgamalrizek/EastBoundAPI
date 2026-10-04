@extends('backend.partials.master')
@section('title') Analytics @endsection
@section('maincontent')
<x-page title="Analytics" :breadcrumb="['Super Admin','Analytics']">
    <div class="row">
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Total Tenants</div><h3 class="mb-0 tv-fw-700">{{ number_format($tenantCount) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">MRR</div><h3 class="mb-0 text-success tv-fw-700">৳{{ number_format($mrr) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">ARR</div><h3 class="mb-0 tv-fw-700">৳{{ number_format($arr) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">ARPU</div><h3 class="mb-0 tv-fw-700">৳{{ $tenantCount ? number_format($mrr / $tenantCount) : 0 }}</h3></div></div></div>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <h4 class="title-site mb-3">Tenants by Plan</h4>
            <x-data-table :headers="['Plan','Tenants']">
                @foreach($byPlan as $p)
                    <tr><td><b>{{ $p->name }}</b></td><td><span class="bullet-badge bullet-badge-info">{{ $p->tenants_count }}</span></td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-6">
            <h4 class="title-site mb-3">Subscriptions by Status</h4>
            <x-data-table :headers="['Status','Count','Amount']">
                @foreach($bySubStatus as $s)
                    <tr><td>{{ ucfirst($s->status) }}</td><td>{{ $s->total }}</td><td>৳{{ number_format($s->amount) }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-12"><div class="card"><div class="card-header"><h4 class="title-site mb-0">Tenant Status Breakdown</h4></div><div class="card-body"><div id="saasAna"></div></div></div></div>
    </div>
</x-page>
@endsection
@push('scripts')
<script type="application/json" id="flow-data">
{
  "charts": [
    {
      "target": "#saasAna",
      "series": [{"name":"Tenants","data": {!! json_encode($byStatus->values()) !!} }],
      "categories": {!! json_encode($byStatus->keys()->map(fn($k)=>ucfirst($k))) !!}
    }
  ]
}
</script>
@endpush
