@extends('backend.partials.master')
@section('title') {{ ___('label.knowledge_base') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.knowledge_base') }}" :breadcrumb="['Support','Knowledge Base']">

    @if(hasPermission('support_create'))
    <x-slot name="action">
        <a href="{{ route('support.kb.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Title','Category','Views','Status','Action']">
        @foreach($items as $x)
            @php $c = $x->status === 'published' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $x->id }}">
                <td><b>{{ $x->title }}</b></td>
                <td>{{ $x->category }}</td>
                <td>{{ number_format($x->views) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($x->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('support_update'))
                        <a href="{{ route('support.kb.edit', $x->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('support_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('support.kb.delete', $x->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $x->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
