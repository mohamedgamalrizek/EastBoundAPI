@extends('backend.partials.master')
@section('title') {{ ___('label.event_bookings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.event_bookings') }}" :breadcrumb="['Events & Tours','Bookings']">

    @if(hasPermission('event_tour_create'))
    <x-slot name="action">
        <a href="{{ route('event-tour.booking.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Booking No','Event','Customer','Seats','Amount','Status','Action']">
        @foreach($items as $b)
            @php
                $c = match($b->status) {
                    'Paid'      => 'success',
                    'Confirmed' => 'info',
                    'Cancelled' => 'danger',
                    default     => 'warning',
                };
            @endphp
            <tr id="row_{{ $b->id }}">
                <td><b>{{ $b->booking_no }}</b></td>
                <td>{{ $b->eventTour?->title ?? '—' }}</td>
                <td>{{ $b->customer_name }}</td>
                <td>{{ $b->seats }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->amount, 2) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $b->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('event_tour_update'))
                        <a href="{{ route('event-tour.booking.edit', $b->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('event_tour_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('event-tour.booking.delete', $b->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $b->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
