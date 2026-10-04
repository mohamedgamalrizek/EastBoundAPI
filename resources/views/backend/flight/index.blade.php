@extends('backend.partials.master')
@section('title') Flight Bookings @endsection
@section('maincontent')
<x-page title="Flight Bookings" :breadcrumb="['Flight','Bookings']">

    @if(hasPermission('flight_create'))
    <x-slot name="action">
        <a href="{{ route('flight.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Flight bookings overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['PNR','Passenger','Airline','Route','Flight Date','Ticket No','Fare','Status','Action']">
        @foreach($items as $b)
            @php $c = in_array($b->status, ['Confirmed','Reissued']) ? 'success' : ($b->status === 'Pending' ? 'warning' : 'danger'); @endphp
            <tr id="row_{{ $b->id }}">
                <td><b>{{ $b->pnr }}</b></td>
                <td>{{ $b->passenger_name }}</td>
                <td>{{ $b->airline }}</td>
                <td>{{ $b->route }}</td>
                <td>{{ $b->flight_date?->format('d M Y') }}</td>
                <td>{{ $b->ticket_no ?? '—' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->fare) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $b->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('flight.booking', $b->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('flight_update'))
                        <a href="{{ route('flight.edit', $b->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('flight_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('flight.delete', $b->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $b->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
