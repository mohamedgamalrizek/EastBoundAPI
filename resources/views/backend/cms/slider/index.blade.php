@extends('backend.partials.master')
@section('title') Sliders @endsection
@section('maincontent')
<x-page title="Sliders" :breadcrumb="['CMS','Sliders']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.slider.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Title','Subtitle','Sort Order','Status','Action']">
        @foreach($items as $s)
            @php $c = $s->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $s->id }}">
                <td><b>{{ $s->title }}</b></td>
                <td>{{ $s->subtitle }}</td>
                <td>{{ $s->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($s->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.slider.edit', $s->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.slider.delete', $s->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $s->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
