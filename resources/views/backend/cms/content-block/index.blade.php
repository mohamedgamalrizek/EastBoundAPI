@extends('backend.partials.master')
@section('title') {{ ___('label.website_content_blocks') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.website_content_blocks') }}" :breadcrumb="[___('menus.cms'), ___('menus.content_blocks')]">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.content-block.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <div class="alert alert-info py-2 text-14">
        <i class="fa fa-circle-info me-1"></i>
        {{ ___('label.content_block_index_hint') }}
    </div>

    <x-data-table :headers="[___('label.section'), ___('label.title'), ___('label.icon'), ___('label.link'), ___('label.order'), ___('label.status'), ___('label.action')]">
        @foreach($items as $item)
            @php $c = $item->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $item->id }}">
                <td>
                    <span class="badge-tv">{{ \App\Models\ContentBlock::SECTIONS[$item->section] ?? $item->section }}</span>
                </td>
                <td>
                    <b>{{ $item->title }}</b>
                    @if($item->body)<div class="text-muted small">{{ Str::limit($item->body, 80) }}</div>@endif
                </td>
                <td>@if($item->icon)<i class="fa-solid {{ $item->icon }}"></i> <span class="text-muted small">{{ $item->icon }}</span>@else — @endif</td>
                <td class="text-muted small">{{ $item->url ?: '—' }}</td>
                <td>{{ $item->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.content-block.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.content-block.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
