@extends('backend.partials.master')
@section('title') Transport Services @endsection
@section('maincontent')
<x-page title="Transport Services" :breadcrumb="['CMS','Transport Services']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.transport-service.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-data-table :headers="['Service','Books as','From','Order','Status','Action']">
        @foreach($items as $item)
            @php $c = $item->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $item->id }}">
                <td>
                    <b><i class="fa-solid {{ $item->icon ?: 'fa-van-shuttle' }} me-1"></i> {{ $item->title }}</b>
                    <div class="text-muted small">{{ Str::limit($item->description, 70) }}</div>
                </td>
                <td>{{ $item->booking_type }}</td>
                <td>{{ currency_symbol() }}{{ number_format($item->price_from) }}{{ $item->price_unit }}</td>
                <td>{{ $item->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.transport-service.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.transport-service.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
