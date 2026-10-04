@extends('backend.partials.master')
@section('title') Hotel Vouchers @endsection
@section('maincontent')
<x-page title="Vouchers" :breadcrumb="['Hotel','Vouchers']">

    {{-- Vouchers are issued for confirmed bookings only. --}}
    <x-data-table :headers="['Voucher','Booking #','Guest','Hotel','City','Room','Check-in','Amount','Status','Action']">
        @foreach($bookings as $b)
            <tr>
                <td><b>VCH-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</b></td>
                <td>{{ $b->booking_no }}</td>
                <td>{{ $b->guest_name }}</td>
                <td>{{ $b->hotel?->name ?? '—' }}</td>
                <td>{{ $b->hotel?->city ?? '—' }}</td>
                <td>{{ $b->hotelRoom->room_type ?? '—' }}</td>
                <td>{{ $b->check_in?->format('d M Y') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount) }}</td>
                <td><span class="bullet-badge bullet-badge-success">Issued</span></td>
                <td>
                    <a href="{{ route('hotel.voucher.pdf', $b->id) }}" target="_blank" rel="noopener"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-download mr-1"></i>PDF
                    </a>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
