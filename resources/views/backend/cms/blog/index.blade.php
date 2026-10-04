@extends('backend.partials.master')
@section('title') {{ ___('label.blogs') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.blogs') }}" :breadcrumb="[___('menus.cms'), ___('label.blogs')]">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.blog.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="{{ ___('label.overview') }}" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="[___('label.title'), ___('label.slug'), ___('label.author'), ___('label.status'), ___('label.published_at'), ___('label.action')]">
        @foreach($items as $b)
            @php $c = $b->status === 'published' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $b->id }}">
                <td><b>{{ $b->title }}</b></td>
                <td>{{ $b->slug }}</td>
                <td>{{ $b->author }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($b->status) }}</span></td>
                <td>{{ $b->published_at ? $b->published_at->format('Y-m-d') : '' }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.blog.edit', $b->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.blog.delete', $b->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $b->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
