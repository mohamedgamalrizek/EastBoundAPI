@extends('backend.partials.master')
@section('title') {{ ___('menus.travelers') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.travelers') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.travelers')]">

    <x-slot name="action">
        <a href="{{ route('cust.travelers.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>

    <x-data-table :headers="[___('label.name'), ___('label.relation'), ___('label.passport'), ___('label.nationality'), ___('label.dob'), ___('label.status'), ___('label.action')]">
        @foreach($travelers as $t)
            @php $c = $t->status === 'Active' ? 'success' : 'danger'; @endphp
            <tr id="row_{{ $t->id }}">
                <td><b>{{ $t->name }}</b></td>
                <td>{{ $t->relation }}</td>
                <td>{{ $t->passport_no }}</td>
                <td>{{ $t->nationality }}</td>
                <td>{{ $t->dob?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $t->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('cust.travelers.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cust.travelers.delete', $t->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $t->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
