@extends('backend.partials.master')
@section('title') {{ ___('label.hr') }} {{ ___('label.leave') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.leave') }}" :breadcrumb="['HR','Leave']">

    @if(hasPermission('hr_create'))
    <x-slot name="action">
        <a href="{{ route('hr.leave.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Staff Name','Leave Type','From Date','To Date','Days','Status','Action']">
        @foreach($items as $r)
            @php $c = $r->status === 'Approved' ? 'success' : ($r->status === 'Rejected' ? 'danger' : 'warning'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->staff_name }}</b></td>
                <td>{{ $r->leave_type }}</td>
                <td>{{ $r->from_date?->format('Y-m-d') }}</td>
                <td>{{ $r->to_date?->format('Y-m-d') }}</td>
                <td>{{ $r->days }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hr_update'))
                        <a href="{{ route('hr.leave.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hr_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hr.leave.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
