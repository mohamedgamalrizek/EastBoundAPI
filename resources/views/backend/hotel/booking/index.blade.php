@extends('backend.partials.master')
@section('title') Hotel Bookings @endsection
@section('maincontent')
<x-page title="Hotel Bookings" :breadcrumb="['Hotel','Bookings']">

    @if(hasPermission('hotel_create'))
    <x-slot name="action">
        <a href="{{ route('hotel.booking.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Booking No','Hotel','Guest Name','Room Type','Check-in','Check-out','Nights','Amount','Status','Action']">
        @foreach($items as $b)
            @php $c = $b->status === 'Confirmed' ? 'success' : ($b->status === 'Cancelled' ? 'danger' : 'warning'); @endphp
            <tr id="row_{{ $b->id }}">
                <td><b>{{ $b->booking_no }}</b></td>
                <td>{{ $b->hotel?->name }}</td>
                <td>{{ $b->guest_name }}</td>
                <td>{{ $b->hotelRoom->room_type ?? '—' }}</td>
                <td>{{ $b->check_in?->format('Y-m-d') }}</td>
                <td>{{ $b->check_out?->format('Y-m-d') }}</td>
                <td>{{ $b->nights }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount, 2) }}</td>
                <td>
                    <span class="bullet-badge bullet-badge-{{ $c }}">{{ $b->status }}</span>
                    @if($b->payment_claimed_at)
                        <br><small class="text-muted" title="{{ $b->payment_claimed_at }}">Payment claimed</small>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hotel_update'))
                        <a href="{{ route('hotel.booking.edit', $b->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hotel_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hotel.booking.delete', $b->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $b->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
