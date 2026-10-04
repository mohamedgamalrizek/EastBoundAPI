@extends('backend.partials.master')
@section('title') {{ ___('menus.commissions') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.commissions') }}" :breadcrumb="[___('menus.agent_portal'), ___('menus.commissions')]">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.earned') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($earned) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.pending') }}</div><h3 class="mb-0 text-warning tv-fw-700">{{ currency_symbol() }}{{ number_format($pending) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.paid_out') }}</div><h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($paid) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.avg_rate') }}</div><h3 class="mb-0 tv-fw-700">{{ number_format($rate, 1) }}%</h3></div></div></div>
    </div>

    <x-data-table :headers="[___('label.reference'), ___('label.booking'), ___('label.customer'), ___('label.rate'), ___('label.commission'), ___('label.earned'), ___('label.status')]">
        @foreach($commissions as $c)
            @php $cls = $c->status === 'paid' ? 'success' : 'warning'; @endphp
            <tr>
                <td><b>{{ $c->reference }}</b></td>
                <td>{{ $c->booking_ref }}</td>
                <td>{{ $c->customer_name }}</td>
                <td>{{ rtrim(rtrim(number_format($c->rate, 2), '0'), '.') }}%</td>
                <td>{{ currency_symbol() }}{{ number_format($c->amount) }}</td>
                <td>{{ $c->earned_on?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($c->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
