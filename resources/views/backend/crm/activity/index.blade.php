@extends('backend.partials.master')
@section('title') {{ ___('label.crm') }} {{ ___('label.activity') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.activity') }}" :breadcrumb="['CRM','Activities']">

    @if(hasPermission('crm_create'))
    <x-slot name="action">
        <a href="{{ route('crm.activity.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Customer','Type','Subject','Channel','Activity Date','Action']">
        @foreach($items as $a)
            <tr id="row_{{ $a->id }}">
                <td><b>{{ $a->customer->name ?? $a->customer_name }}</b></td>
                <td>{{ ucfirst($a->type) }}</td>
                <td>{{ $a->subject }}</td>
                <td>{{ $a->channel }}</td>
                <td>{{ $a->activity_date ? $a->activity_date->format('Y-m-d') : '' }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('crm_update'))
                        <a href="{{ route('crm.activity.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('crm_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('crm.activity.delete', $a->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $a->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
