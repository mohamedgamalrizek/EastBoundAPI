@extends('backend.partials.master')
@section('title') {{ ___('menus.vehicle_categories') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.vehicle_categories') }}" :breadcrumb="[___('label.transport'), ___('menus.vehicle_categories')]">

    @if(hasPermission('transport_create'))
    <x-slot name="action">
        <a href="{{ route('transport.vehicle-category.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-data-table :headers="[___('label.name'), ___('label.order'), ___('label.status'), ___('label.action')]">
        @forelse($items as $item)
            @php $c = $item->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $item->id }}">
                <td><b>{{ $item->name }}</b></td>
                <td>{{ $item->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('transport_update'))
                        <a href="{{ route('transport.vehicle-category.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('transport_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('transport.vehicle-category.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <x-nodata-found :colspan="4" />
        @endforelse
    </x-data-table>

</x-page>
@endsection
