@extends('backend.partials.master')
@section('title') {{ ___('label.tour') }} {{ ___('label.schedule') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.schedule') }}" :breadcrumb="['Tour','Schedules']">

    @if(hasPermission('tour_create'))
    <x-slot name="action">
        <a href="{{ route('tour.schedule.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Tour schedules overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Package','Start Date','End Date','Seats','Booked','Status','Action']">
        @foreach($items as $x)
            @php $c = $x->status_class; @endphp
            <tr id="row_{{ $x->id }}">
                <td><b>{{ $x->package->title ?? $x->package_title }}</b></td>
                <td>{{ $x->start_date?->format('Y-m-d') }}</td>
                <td>{{ $x->end_date?->format('Y-m-d') }}</td>
                <td>{{ $x->seats }}</td>
                <td>{{ $x->booked }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($x->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('tour_update'))
                        <a href="{{ route('tour.schedule.edit', $x->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('tour_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('tour.schedule.delete', $x->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $x->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
