@extends('pdf.layout', [
    'documentTitle'      => 'Hotel Voucher',
    'documentNo'         => $booking->booking_no,
    'documentStatus'     => $booking->status,
    'documentStatusTone' => match($booking->status) {
        'Paid', 'Confirmed' => 'paid',
        'Cancelled'         => 'void',
        default             => 'due',
    },
])

@section('body')
@php
    $symbol   = currency_symbol();
    $nights   = max(1, (int) $booking->nights);
    $perNight = (float) $booking->amount / $nights;
    $location = collect([optional($booking->hotel)->city, optional($booking->hotel)->country])->filter()->join(', ');
@endphp

<table class="facts">
    <tr>
        <td>
            <div class="label">Guest</div>
            <div class="value">{{ $booking->guest_name }}</div>
        </td>
        <td>
            <div class="label">Hotel</div>
            <div class="value">{{ optional($booking->hotel)->name ?: '—' }}</div>
            @if($location)<div class="brand-meta">{{ $location }}</div>@endif
        </td>
    </tr>
</table>

<h2>Stay</h2>
<table class="grid">
    <thead>
        <tr>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Nights</th>
            <th>Room</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ dateFormat($booking->check_in) }}</td>
            <td>{{ dateFormat($booking->check_out) }}</td>
            <td>{{ $nights }}</td>
            <td>{{ optional($booking->hotelRoom)->room_type ?: '—' }}</td>
        </tr>
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Per night</td>
        <td class="num">{{ $symbol }}{{ number_format($perNight, 2) }}</td>
    </tr>
    <tr>
        <td>Nights</td>
        <td class="num">{{ $nights }}</td>
    </tr>
    <tr class="grand">
        <td>Total</td>
        <td class="num">{{ $symbol }}{{ number_format((float) $booking->amount, 2) }}</td>
    </tr>
</table>

<div class="note">
    Present this voucher at check-in together with the guest's photo ID or passport.
    Room allocation is at the hotel's discretion and subject to availability on arrival.
</div>
@endsection
