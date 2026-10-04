@extends('pdf.layout', [
    'documentTitle'      => 'E-Ticket',
    'documentNo'         => $ticket->ticket_no ?: $ticket->pnr,
    'documentStatus'     => $ticket->status,
    'documentStatusTone' => match($ticket->status) {
        'Confirmed'            => 'paid',
        'Cancelled', 'Refunded' => 'void',
        default                => 'due',
    },
])

@section('body')
@php
    $symbol = currency_symbol();
@endphp

<table class="facts">
    <tr>
        <td>
            <div class="label">Passenger</div>
            <div class="value">{{ $ticket->passenger_name }}</div>
        </td>
        <td>
            <div class="label">Booking reference (PNR)</div>
            <div class="value">{{ $ticket->pnr ?: '—' }}</div>
        </td>
    </tr>
</table>

<h2>Flight</h2>
<table class="grid">
    <thead>
        <tr>
            <th>Airline</th>
            <th>Route</th>
            <th>Date</th>
            <th>Ticket no</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $ticket->airline ?: '—' }}</td>
            <td>{{ $ticket->route ?: '—' }}</td>
            <td>{{ $ticket->flight_date ? dateFormat($ticket->flight_date) : '—' }}</td>
            <td>{{ $ticket->ticket_no ?: '—' }}</td>
        </tr>
    </tbody>
</table>

<table class="totals">
    <tr class="grand">
        <td>Fare</td>
        <td class="num">{{ $symbol }}{{ number_format((float) $ticket->fare, 2) }}</td>
    </tr>
</table>

@if(in_array($ticket->status, ['Cancelled', 'Refunded'], true))
    <div class="note">
        This ticket is {{ strtolower($ticket->status) }} and is not valid for travel.
        It is reproduced for your records only.
    </div>
@else
    <div class="note">
        Check in at least 3 hours before departure for international flights, 2 hours for domestic.
        Carry the passport or photo ID the ticket was issued against — the name must match exactly.
        Baggage allowance and fare rules are set by the airline.
    </div>
@endif
@endsection
