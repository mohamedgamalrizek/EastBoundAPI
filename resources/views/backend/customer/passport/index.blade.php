@extends('backend.partials.master')
@section('title') {{ ___('label.customer') }} {{ ___('label.passport') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.passport') }}" :breadcrumb="['Customer','Passport']">

    @if(hasPermission('customer_create'))
    <x-slot name="action">
        <a href="{{ route('customer.passport.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Holder Name','Passport No','Customer','Nationality','Issue Date','Expiry Date','Status','Action']">
        @foreach($items as $p)
            @php $c = $p->status === 'Valid' ? 'success' : ($p->status === 'Expiring' ? 'warning' : 'danger'); @endphp
            <tr id="row_{{ $p->id }}">
                <td><b>{{ $p->holder_name }}</b></td>
                <td>{{ $p->passport_no }}</td>
                <td>{{ $p->customer->name ?? '' }}</td>
                <td>{{ $p->nationality }}</td>
                <td>{{ $p->issue_date ? $p->issue_date->format('Y-m-d') : '' }}</td>
                <td>{{ $p->expiry_date ? $p->expiry_date->format('Y-m-d') : '' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('customer_update'))
                        <a href="{{ route('customer.passport.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('customer_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('customer.passport.delete', $p->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $p->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
