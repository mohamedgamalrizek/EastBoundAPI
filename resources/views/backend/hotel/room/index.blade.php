@extends('backend.partials.master')
@section('title') {{ ___('label.hotel') }} {{ ___('label.room') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.room') }}" :breadcrumb="['Hotel','Rooms']">

    @if(hasPermission('hotel_create'))
    <x-slot name="action">
        <a href="{{ route('hotel.room.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Hotel','Room Type','Capacity','Rate / night','Total Rooms','Available Rooms','Status','Action']">
        @foreach($items as $r)
            @php $c = $r->status === 'available' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->hotel->name ?? '' }}</b></td>
                <td>{{ $r->room_type }}</td>
                <td>{{ $r->capacity }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->rate_per_night) }}</td>
                <td>{{ $r->total_rooms }}</td>
                <td>{{ $r->available_rooms }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($r->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hotel_update'))
                        <a href="{{ route('hotel.room.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hotel_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hotel.room.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
