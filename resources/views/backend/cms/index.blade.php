@extends('backend.partials.master')
@section('title') {{ ___('menus.cms') }} {{ ___('label.pages') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.cms') }} {{ ___('label.pages') }}" :breadcrumb="[___('menus.cms'), ___('label.pages')]">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-data-table :headers="[___('label.title'), ___('label.slug'), ___('label.status'), ___('label.action')]">
        @foreach($items as $page)
            @php $c = $page->status === 'published' ? 'success' : 'warning'; @endphp
            <tr id="row_{{ $page->id }}">
                <td><b>{{ $page->title }}</b></td>
                <td>{{ $page->slug }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($page->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.edit', $page->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.delete', $page->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $page->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
