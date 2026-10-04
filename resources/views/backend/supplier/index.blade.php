@extends('backend.partials.master')
@section('title') Suppliers @endsection
@section('maincontent')
<x-page title="Suppliers" :breadcrumb="['Supplier','Suppliers']">

    @if(hasPermission('supplier_create'))
    <x-slot name="action">
        <a href="{{ route('supplier.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Suppliers overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','Type','Contact Person','Phone','Email','Balance','Status','Action']">
        @foreach($items as $s)
            @php $c = $s->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $s->id }}">
                <td><b>{{ $s->name }}</b></td>
                <td>{{ $s->type }}</td>
                <td>{{ $s->contact_person }}</td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ currency_symbol() }}{{ number_format($s->balance, 2) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($s->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('supplier_update'))
                        <a href="{{ route('supplier.edit', $s->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('supplier_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('supplier.delete', $s->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $s->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
