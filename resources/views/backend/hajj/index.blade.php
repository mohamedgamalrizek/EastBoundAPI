@extends('backend.partials.master')
@section('title') Hajj Packages @endsection
@section('maincontent')
<x-page title="Hajj Packages" :breadcrumb="['Hajj','Packages']">

    @if(hasPermission('hajj_create'))
    <x-slot name="action">
        <a href="{{ route('hajj.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon/plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-list-analytics
        title="Hajj packages overview"
        :stats="$analytics['stats']"
        :donut="$analytics['donut']"
        :trend="$analytics['trend']" />

    <x-data-table :headers="['Package No','Title','Type','Duration','Price','Seats','Status','Action']">
        @foreach($items as $x)
            @php $c = $x->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $x->id }}">
                <td><b>{{ $x->package_no }}</b></td>
                <td>{{ $x->title }}</td>
                <td>{{ $x->type }}</td>
                <td>{{ $x->duration_days }} {{ ___('label.days') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($x->price) }}</td>
                <td>{{ $x->seats }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($x->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('hajj_update'))
                        <a href="{{ route('hajj.edit', $x->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('hajj_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('hajj.delete', $x->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $x->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
