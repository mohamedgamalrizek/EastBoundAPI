@extends('backend.partials.master')
@section('title') SaaS Payments @endsection
@section('maincontent')
<x-page title="SaaS Payments" :breadcrumb="['Super Admin','Payments']">

    <div class="row">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Collected</div><h3 class="mb-0 text-success tv-fw-700">৳{{ number_format($totalPaid) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Pending (trials)</div><h3 class="mb-0 text-warning tv-fw-700">৳{{ number_format($pending) }}</h3></div></div></div>
    </div>

    <x-data-table :headers="['Payment','Tenant','Plan','Date','Amount','Status']">
        @foreach($payments as $p)
            @php $c = $p->status === 'active' ? 'success' : 'danger'; @endphp
            <tr>
                <td><b>SPAY-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ optional($p->tenant)->name ?? '—' }}</td>
                <td>{{ optional($p->plan)->name ?? '—' }}</td>
                <td>{{ $p->starts_at?->format('d M Y') ?? '—' }}</td>
                <td>৳{{ number_format($p->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($p->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
