@extends('backend.partials.master')
@section('title') Points & Referrals @endsection
@section('maincontent')
<x-page title="Points & Referrals" :breadcrumb="['Customer Portal','Points & Referrals']">

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="tv-card"><div class="tv-card-body">
                <small>Available points</small>
                <h2 class="mb-0">{{ number_format($balance) }}</h2>
                <small class="text-success">Worth {{ currency_symbol() }}{{ number_format($worth, 2) }}</small>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="tv-card"><div class="tv-card-body">
                <small>Your referral code</small>
                <h3>{{ $code }}</h3>
                <input class="form-control" readonly value="{{ route('register', ['ref' => $code]) }}">
            </div></div>
        </div>
    </div>

    {{-- Points are spent as a discount when booking, not paid into the
         wallet: the wallet is a real liability account the ledger reconciles
         against money actually taken. --}}
    <div class="alert {{ $rate > 0 ? 'alert-info' : 'alert-warning' }}">
        @if($rate > 0)
            <b>How to spend them.</b>
            Each point is worth {{ currency_symbol() }}{{ number_format($rate, 2) }} off a tour booking.
            Redeem from {{ number_format($minRedeem) }} points at a time, covering up to
            {{ $maxPercent }}% of a booking — enter them in the Book box on the
            <a href="{{ route('cust.tours') }}">Tours</a> page.
            @if($balance >= $minRedeem)
                You have enough to redeem now.
            @else
                You need {{ number_format(max(0, $minRedeem - $balance)) }} more point(s) to redeem.
            @endif
        @else
            Points redemption is currently switched off. Your balance is safe and can be spent once it is enabled.
        @endif
    </div>

    <x-data-table :headers="[___('label.date'), ___('label.description'), 'Points']">
        @forelse($transactions as $t)
            <tr>
                <td>{{ $t->created_at->format('d M Y') }}</td>
                <td>{{ $t->description }}</td>
                <td class="{{ $t->points >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $t->points > 0 ? '+' : '' }}{{ number_format($t->points) }}
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">No points activity yet.</td></tr>
        @endforelse
    </x-data-table>

</x-page>
@endsection
