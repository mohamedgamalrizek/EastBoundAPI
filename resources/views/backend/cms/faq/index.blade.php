@extends('backend.partials.master')
@section('title') {{ ___('label.faq') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.faq') }}" :breadcrumb="[___('menus.cms'), ___('label.faq')]">

    @if(hasPermission('cms_create'))
    <x-slot name="action">
        <a href="{{ route('cms.faq.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="{{ ___('label.overview') }}" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="[___('label.question'), ___('label.category'), ___('label.status'), ___('label.action')]">
        @foreach($items as $f)
            @php $c = $f->status === 'active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $f->id }}">
                <td><b>{{ $f->question }}</b></td>
                <td>{{ $f->category }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($f->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('cms_update'))
                        <a href="{{ route('cms.faq.edit', $f->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('cms_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.faq.delete', $f->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $f->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
