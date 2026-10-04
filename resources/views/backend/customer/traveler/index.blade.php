@extends('backend.partials.master')
@section('title') {{ ___('label.customer') }} {{ ___('label.traveler') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.traveler') }}" :breadcrumb="['Customer','Travelers']">

    @if(hasPermission('customer_create'))
    <x-slot name="action">
        <a href="{{ route('customer.traveler.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Name','Customer','Relation','Passport No','Nationality','Status','Action']">
        @foreach($items as $t)
            @php $c = $t->status === 'Active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->name }}</b></td>
                <td>{{ $t->customer->name ?? '' }}</td>
                <td>{{ $t->relation }}</td>
                <td>{{ $t->passport_no }}</td>
                <td>{{ $t->nationality }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $t->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('customer_update'))
                        <a href="{{ route('customer.traveler.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('customer_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('customer.traveler.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
