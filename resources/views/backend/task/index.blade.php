@extends('backend.partials.master')
@section('title') Tasks @endsection
@section('maincontent')
<x-page title="Tasks" :breadcrumb="['Task','Tasks']">

    @if(hasPermission('task_create'))
    <x-slot name="action">
        <a href="{{ route('task.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Title','Assigned To','Project','Priority','Due Date','Status','Action']">
        @foreach($items as $t)
            @php
                $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Medium' ? 'warning' : 'success');
                $sc = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
            @endphp
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->title }}</b></td>
                <td>{{ $t->assignedTo->name ?? '—' }}</td>
                <td>{{ $t->project }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                <td>{{ $t->due_date ? $t->due_date->format('Y-m-d') : '' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('task_update'))
                        <a href="{{ route('task.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('task_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('task.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
