@extends('backend.partials.master')
@section('title') SaaS Invoices @endsection
@section('maincontent')
<x-page title="SaaS Invoices" :breadcrumb="['Super Admin','Invoices']">

    <div class="row">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Total Billed</div><h3 class="mb-0 text-success tv-fw-700">৳{{ number_format($totalBilled) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Invoices</div><h3 class="mb-0 tv-fw-700">{{ $subscriptions->count() }}</h3></div></div></div>
    </div>

    <p class="text-muted">Invoices are generated from active subscriptions. A payment-gateway integration would attach real transactions.</p>

    <x-data-table :headers="['Invoice','Tenant','Plan','Date','Amount','Status']">
        @foreach($subscriptions as $sub)
            @php $c = $sub->status === 'active' ? 'success' : ($sub->status === 'expired' || $sub->status === 'cancelled' ? 'danger' : 'warning'); @endphp
            <tr>
                <td><b>SINV-{{ str_pad($sub->id, 4, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ optional($sub->tenant)->name ?? '—' }}</td>
                <td>{{ optional($sub->plan)->name ?? '—' }}</td>
                <td>{{ $sub->starts_at?->format('d M Y') ?? '—' }}</td>
                <td>৳{{ number_format($sub->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($sub->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
