@extends('backend.partials.master')
@section('title') {{ ___('label.cancellation_requests') }} @endsection
@section('maincontent')
<x-page :title="___('label.cancellation_requests')" :breadcrumb="[___('label.flight'), ___('label.cancellation')]">

    @include('backend.flight.partials.settle-list', ['eligible' => $eligible, 'action' => $action])

    <x-data-table :headers="[
        ___('label.pnr'), ___('label.ticket_no'), ___('label.passenger'), ___('label.airline'),
        ___('label.route'), ___('label.flight_date'), ___('label.fare'), ___('label.reason')
    ]">
        @forelse($bookings as $b)
            <tr>
                <td><b>{{ $b->pnr }}</b></td>
                <td>{{ $b->ticket_no ?? '—' }}</td>
                <td>{{ $b->passenger_name }}</td>
                <td>{{ $b->airline }}</td>
                <td>{{ $b->route }}</td>
                <td>{{ $b->flight_date?->format('d M Y') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->fare) }}</td>
                <td class="text-muted">{{ $b->status_note ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
        @endforelse
    </x-data-table>

</x-page>
@endsection
