@extends('backend.partials.master')
@section('title') Branches @endsection
@section('maincontent')
<x-page title="Branches" :breadcrumb="['Branches']">

    @if(hasPermission('branch_create'))
    <x-slot name="action">
        <a href="{{ route('branch.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>Add</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Branches overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Branch Name','Code','Manager','Phone','Email','City','Status','Action']">
        @foreach($items as $r)
            @php $sc = \Illuminate\Support\Str::contains(strtolower($r->status), ['cancel','reject','inactive','closed','paused','fail']) ? 'danger' : (\Illuminate\Support\Str::contains(strtolower($r->status), ['pend','process','plan','inquiry','open']) ? 'warning' : 'success'); @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->name }}</b></td>
                <td>{{ $r->code }}</td>
                <td>{{ $r->manager_name }}</td>
                <td>{{ $r->phone }}</td>
                <td>{{ $r->email }}</td>
                <td>{{ $r->city }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $r->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('branch_update'))
                        <a href="{{ route('branch.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('branch_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('branch.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="Delete" data-text="This action cannot be reversed." data-confirm-button-text="Delete" data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
