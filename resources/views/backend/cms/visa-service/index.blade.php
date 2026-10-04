@extends('backend.partials.master')
@section('title') Visa Services @endsection
@section('maincontent')
<x-page title="Visa Services" :breadcrumb="['CMS','Visa Services']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.visa-service.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Country','Type','Processing','Fee','Order','Status','Action']">
        @foreach($items as $item)
            @php $c = $item->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $item->id }}">
                <td><b>{{ $item->flag }} {{ $item->country }}</b> @if($item->is_featured)<span class="bullet-badge bullet-badge-info ms-1">Featured</span>@endif</td>
                <td>{{ $item->visa_type }}</td>
                <td>{{ $item->processing_time ?: '—' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($item->fee) }}</td>
                <td>{{ $item->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.visa-service.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.visa-service.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
