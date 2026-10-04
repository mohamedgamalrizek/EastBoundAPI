@extends('backend.partials.master')
@section('title') Payment Records @endsection
@section('maincontent')
<x-page title="Payment Records" :breadcrumb="['Super Admin','Payments']">

    <div class="row">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Collected (success)</div><h3 class="mb-0 text-success tv-fw-700">৳{{ number_format($collected) }}</h3></div></div></div>
    </div>

    <x-data-table :headers="['Reference','Gateway','Plan','Payer','Amount','Status','Txn ID','When']">
        @foreach($payments as $p)
            @php $c = $p->status==='success'?'success':($p->status==='failed'?'danger':($p->status==='cancelled'?'warning':'secondary')); @endphp
            <tr>
                <td><b>{{ $p->reference }}</b></td>
                <td>{{ ucfirst($p->gateway) }}</td>
                <td>{{ optional($p->plan)->name ?? '—' }}</td>
                <td>{{ $p->payer_name }}<br><small class="text-muted">{{ $p->payer_email }}</small></td>
                <td>{{ $p->currency }} {{ number_format($p->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($p->status) }}</span></td>
                <td><small>{{ $p->gateway_ref ?? '—' }}</small></td>
                <td>{{ $p->created_at?->format('d M Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
