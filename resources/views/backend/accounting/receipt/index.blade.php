@extends('backend.partials.master')
@section('title') {{ ___('label.receipt') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.receipt') }}" :breadcrumb="['Accounting','Receipts']">

    @if(hasPermission('accounting_create'))
    <x-slot name="action">
        <a href="{{ route('acc.receipt.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon/plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Receipt No','Invoice','Customer','Received On','Amount','Method','Reference','Action']">
        @foreach($items as $r)
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->receipt_no }}</b></td>
                <td>{{ $r->invoice->invoice_no ?? '—' }}</td>
                <td>{{ $r->customer->name ?? $r->customer_name }}</td>
                <td>{{ $r->received_on ? $r->received_on->format('Y-m-d') : '' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->amount, 2) }}</td>
                <td>{{ $r->method }}</td>
                <td>{{ $r->reference }}</td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('accounting_update'))
                        <a href="{{ route('acc.receipt.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('accounting_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('acc.receipt.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
