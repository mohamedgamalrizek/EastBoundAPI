{{-- Standalone printable hotel voucher. Opens in a new tab and fires the
     browser print dialog, so the user can save it as PDF without a server-side
     PDF renderer. Swap the controller to Pdf::loadView() if dompdf is installed. --}}
@php
    $statusClass = match($booking->status) {
        'Confirmed' => 'status-confirmed',
        'Booked'    => 'status-booked',
        'Cancelled' => 'status-cancelled',
        default     => 'status-default',
    };
    $nights   = max(1, (int) $booking->nights);
    $perNight = (float) $booking->amount / $nights;
    $location = collect([$booking->hotel?->city, $booking->hotel?->country])->filter()->join(', ');
    $brand    = settings('name') ?: 'FLOW';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $voucherNo }} — Hotel Voucher</title>
    <link href="{{ asset('backend/css/print/hotel-voucher.css') }}?v={{ filemtime(public_path('backend/css/print/hotel-voucher.css')) }}" rel="stylesheet">
</head>
<body>

<div class="actions">
    <button type="button" onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="sheet">
    <header class="head">
        <div class="brand">
            {{ $brand }}
            <small>{{ settings('email') }}@if(settings('email') && settings('phone')) · @endif{{ settings('phone') }}</small>
        </div>
        <div class="doc">
            <p class="kicker">Hotel Voucher</p>
            <div class="no">{{ $voucherNo }}</div>
            <span class="status {{ $statusClass }}"><i></i>{{ $booking->status }}</span>
        </div>
    </header>

    <main>
        <div class="guest-strip">
            <div class="label">Guest</div>
            <div class="name">{{ $booking->guest_name }}</div>
        </div>

        <div class="info-grid">
            <div class="field">
                <div class="label">Hotel</div>
                <div class="value">{{ $booking->hotel?->name ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="label">Location</div>
                <div class="value muted">{{ $location ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="label">Room type</div>
                <div class="value">{{ $booking->hotelRoom->room_type ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="label">Booking reference</div>
                <div class="value">{{ $booking->booking_no }}</div>
            </div>
            <div class="field">
                <div class="label">Check-in</div>
                <div class="value">{{ $booking->check_in?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="label">Check-out</div>
                <div class="value">{{ $booking->check_out?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>

        <div class="perforation"></div>

        <div class="stub">
            <div>
                <div class="total-label">Total amount</div>
                <div class="total-value">{{ currency_symbol() }}{{ number_format((float) $booking->amount) }}</div>
                <div class="rate">{{ currency_symbol() }}{{ number_format($perNight) }} <span>&times;</span> <b>{{ $nights }}</b> night{{ $nights === 1 ? '' : 's' }}</div>
            </div>
            <div class="barcode" role="presentation" aria-hidden="true"></div>
        </div>
    </main>

    <div class="notes">
        <b>Please note:</b> Present this voucher together with a valid photo ID at
        check-in. It is valid only for the guest, hotel and dates printed
        above. Standard check-in is 2:00 PM and check-out is 12:00 PM unless the
        hotel confirms otherwise. For any change or cancellation, contact
        {{ $brand }} before your travel date.
    </div>

    <footer class="foot">
        <span>{{ $brand }} — computer generated voucher, no signature required.</span>
        <span>Issued {{ now()->format('d M Y') }} · {{ $voucherNo }}</span>
    </footer>
</div>

<script>
    // Auto-open the print dialog once the page has finished laying out.
    window.addEventListener('load', function () { window.print(); });
</script>

</body>
</html>
