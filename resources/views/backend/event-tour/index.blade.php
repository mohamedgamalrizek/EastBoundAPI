@extends('backend.partials.master')
@section('title') Event & Conference @endsection
@section('maincontent')
<x-page title="Event & Conference" :breadcrumb="['Event & Conference']">

    @if(hasPermission('event_tour_create'))
    <x-slot name="action">
        <a href="{{ route('event-tour.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Event & Conference overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Title','Type','Location','Event Date','Seats','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->title }}</b></td>
                <td>{{ $r->type }}</td>
                <td>{{ $r->location }}</td>
                <td>{{ optional($r->event_date) ? \Illuminate\Support\Carbon::parse($r->event_date)->format('d M Y') : '' }}</td>
                <td>{{ $r->seatsLeft() }} / {{ $r->seats }} left</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('event_tour_update'))
                        <a href="{{ route('event-tour.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('event_tour_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('event-tour.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
