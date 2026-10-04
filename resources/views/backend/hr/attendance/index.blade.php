@extends('backend.partials.master')
@section('title') {{ ___('label.hr') }} {{ ___('label.attendance') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.attendance') }}" :breadcrumb="['HR','Attendance']">

    @if(hasPermission('hr_create'))
    <x-slot name="action">
        <a href="{{ route('hr.attendance.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Staff Name','Date','Check-in','Check-out','Hours','Status','Action']">
        @foreach($items as $a)
            @php $c = $a->status === 'Present' ? 'success' : ($a->status === 'Leave' ? 'warning' : 'danger'); @endphp
            <tr id="row_{{ $a->id }}">
                <td><b>{{ $a->staff_name }}</b></td>
                <td>{{ $a->date ? $a->date->format('Y-m-d') : '' }}</td>
                <td>{{ $a->check_in }}</td>
                <td>{{ $a->check_out }}</td>
                <td>{{ $a->hours }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $a->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hr_update'))
                        <a href="{{ route('hr.attendance.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hr_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hr.attendance.delete', $a->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $a->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
