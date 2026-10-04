@extends('backend.partials.master')
@section('title') Support Tickets @endsection
@section('maincontent')
<x-page title="Support Tickets" :breadcrumb="['Support','Tickets']">

    @if(hasPermission('support_create'))
    <x-slot name="action">
        <a href="{{ route('support.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Ticket No','Subject','Customer','Priority','Department','Status','Action']">
        @foreach($items as $t)
            @php
                $sc = $t->status === 'Closed' ? 'success' : ($t->status === 'Pending' ? 'warning' : 'primary');
                $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Medium' ? 'warning' : 'info');
            @endphp
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->ticket_no }}</b></td>
                <td>{{ $t->subject }}</td>
                <td>{{ $t->customer_name }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                <td>{{ $t->department }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('support.ticket', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                        @if(hasPermission('support_update'))
                        <a href="{{ route('support.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('support_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('support.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
