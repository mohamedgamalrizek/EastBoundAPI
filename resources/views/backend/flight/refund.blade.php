@extends('backend.partials.master')
@section('title') {{ ___('label.refund_tracking') }} @endsection
@section('maincontent')
<x-page :title="___('label.refund_tracking')" :breadcrumb="[___('label.flight'), ___('label.refund_tracking')]">

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.awaiting_refund') }}</div>
            <h3 class="mb-0 text-warning">{{ $eligible->count() }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.refunded') }}</div>
            <h3 class="mb-0">{{ currency_symbol() }}{{ number_format($refunded) }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.penalty_retained') }}</div>
            <h3 class="mb-0 text-danger">{{ currency_symbol() }}{{ number_format($retained) }}</h3>
        </div></div></div>
    </div>

    @include('backend.flight.partials.settle-list', ['eligible' => $eligible, 'action' => $action])

    {{-- The old page showed the original fare here; a refund is almost never
         the full fare, so both figures are listed. --}}
    <x-data-table :headers="[
        ___('label.pnr'), ___('label.ticket_no'), ___('label.passenger'), ___('label.route'),
        ___('label.fare'), ___('label.refund_amount'), ___('label.penalty'), ___('label.date')
    ]">
        @forelse($bookings as $b)
            <tr>
                <td><b>{{ $b->pnr }}</b></td>
                <td>{{ $b->ticket_no ?? '—' }}</td>
                <td>{{ $b->passenger_name }}</td>
                <td>{{ $b->route }}</td>
                <td class="text-muted">{{ currency_symbol() }}{{ number_format($b->fare) }}</td>
                <td><b>{{ currency_symbol() }}{{ number_format((float) $b->refund_amount) }}</b></td>
                <td class="text-danger">{{ currency_symbol() }}{{ number_format((float) $b->penalty) }}</td>
                <td>{{ $b->status_changed_at?->format('d M Y') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
        @endforelse
    </x-data-table>

</x-page>
@endsection
