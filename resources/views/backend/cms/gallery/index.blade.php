@extends('backend.partials.master')
@section('title') {{ ___('label.gallery') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.gallery') }}" :breadcrumb="['CMS','Gallery']">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.gallery.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Image','Title','Image Label','Category','Status','Action']">
        @foreach($items as $g)
            @php $c = $g->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $g->id }}">
                <td>
                    @if($g->image)
                    <img src="{{ media_url($g->image) }}" alt="" class="tv-img-56 tv-object-cover tv-img-radius-6">
                    @else
                    <div class="text-muted d-flex align-items-center justify-content-center tv-placeholder-56">
                        <i class="fa fa-image"></i>
                    </div>
                    @endif
                </td>
                <td><b>{{ $g->title }}</b></td>
                <td>{{ $g->image_label }}</td>
                <td>{{ $g->category }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($g->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.gallery.edit', $g->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.gallery.delete', $g->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $g->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
