@extends('backend.partials.master')
@section('title') {{ ___('label.hajj_pilgrims') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.hajj_pilgrims') }}" :breadcrumb="['Hajj','Pilgrims']">

    @if(hasPermission('hajj_create'))
    <x-slot name="action">
        <a href="{{ route('hajj.pilgrim.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Pilgrim No','Name','Passport No','Package','Group','Payment Status','Document Status','Status','Action']">
        @foreach($items as $p)
            @php $c = $p->status === 'Confirmed' ? 'success' : ($p->status === 'Cancelled' ? 'danger' : 'warning'); @endphp
            <tr id="row_{{ $p->id }}">
                <td><b>{{ $p->pilgrim_no }}</b></td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->passport_no }}</td>
                <td>{{ $p->package_title }}</td>
                <td>{{ $p->group_name }}</td>
                <td>{{ $p->payment_status }}</td>
                <td>{{ $p->document_status }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hajj_update'))
                        <a href="{{ route('hajj.pilgrim.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hajj_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hajj.pilgrim.delete', $p->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $p->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
