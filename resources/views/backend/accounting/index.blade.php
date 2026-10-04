@extends('backend.partials.master')
@section('title') {{ ___('label.accounting') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.accounting') }}" :breadcrumb="['Accounting','Chart of Accounts']">

    @if(hasPermission('accounting_create'))
    <x-slot name="action">
        <a href="{{ route('acc.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Code','Name','Type','Cash/Bank','Opening','Balance','Action']">
        @foreach($items as $acc)
            <tr id="row_{{ $acc->id }}">
                <td><b>{{ $acc->code }}</b></td>
                <td>{{ $acc->name }}</td>
                <td>{{ $acc->type }}</td>
                <td>{{ $acc->cash_type === 'none' ? '—' : ucfirst($acc->cash_type) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($acc->opening_balance, 2) }}</td>
                {{-- Derived from the journal, never typed in. --}}
                <td><b>{{ currency_symbol() }}{{ number_format($acc->balance, 2) }}</b></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('accounting_update'))
                        <a href="{{ route('acc.edit', $acc->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('accounting_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('acc.delete', $acc->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $acc->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
