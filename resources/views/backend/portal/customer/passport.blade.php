@extends('backend.partials.master')
@section('title') {{ ___('label.passport_information') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.passport_information') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.passport')]">

    <x-slot name="action">
        <a href="{{ route('cust.passport.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>

    <x-data-table :headers="[___('label.holder'), ___('label.passport_no'), ___('label.nationality'), ___('label.issue_date'), ___('label.expiry_date'), ___('label.status'), ___('label.action')]">
        @foreach($passports as $p)
            @php
                $map = ['Valid'=>'success','Expiring'=>'warning','Expired'=>'danger'];
                $c = $map[$p->status] ?? 'secondary';
            @endphp
            <tr id="row_{{ $p->id }}">
                <td><b>{{ $p->holder_name }}</b></td>
                <td>{{ $p->passport_no }}</td>
                <td>{{ $p->nationality }}</td>
                <td>{{ $p->issue_date?->format('d M Y') }}</td>
                <td>{{ $p->expiry_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->status }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        <a href="{{ route('cust.passport.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('cust.passport.delete', $p->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $p->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
