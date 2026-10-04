@extends('backend.partials.master')
@section('title') {{ ___('label.menu') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.menu') }}" :breadcrumb="[___('menus.cms'), ___('label.menu')]">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.menu.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="{{ ___('label.overview') }}" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    {{-- Rows arrive depth-first with a tree_depth attribute, so the nesting
         is readable at a glance. --}}
    <x-data-table :headers="[___('label.title'), ___('label.url'), ___('label.region'), ___('label.sort_order'), ___('label.status'), ___('label.action')]">
        @foreach($items as $m)
            @php
                $c     = $m->status === 'active' ? 'success' : 'danger';
                $depth = (int) ($m->tree_depth ?? 0);
            @endphp
            <tr id="row_{{ $m->id }}">
                <td style="padding-left: {{ 12 + $depth * 26 }}px">
                    @if($depth)<span class="text-muted me-1">└</span>@endif
                    @if($m->icon)<i class="fa-solid {{ $m->icon }} me-1 text-muted"></i>@endif
                    @if($depth) {{ $m->title }} @else <b>{{ $m->title }}</b> @endif
                    @if($m->target === '_blank')<i class="fa fa-arrow-up-right-from-square ms-1 text-muted small"></i>@endif
                </td>
                <td>{{ $m->url ?: '—' }}</td>
                <td>{{ \App\Repositories\Menu\MenuRepository::POSITIONS[$m->position] ?? $m->position }}</td>
                <td>{{ $m->sort_order }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($m->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.menu.edit', $m->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.menu.delete', $m->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $m->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
