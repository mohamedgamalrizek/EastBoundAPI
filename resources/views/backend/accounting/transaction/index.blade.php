@extends('backend.partials.master')
@section('title') Account Transactions @endsection
@section('maincontent')
<x-page title="Account Transactions" :breadcrumb="['Accounting','Transactions']">

    @if(hasPermission('accounting_create'))
    <x-slot name="action">
        <a href="{{ route('acc.txn.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    @if(!empty($analytics))
        <x-list-analytics title="Overview" :stats="$analytics['stats']" :donut="$analytics['donut']" :trend="$analytics['trend']" />
    @endif

    <x-data-table :headers="['Date','Debit','Credit','Type','Amount','Reference','Source','Action']">
        @foreach($items as $txn)
            @php
                $c = in_array($txn->type, ['income','receipt']) ? 'success' : 'danger';
                $debit  = $txn->debitAccountId()  === $txn->account_id ? ($txn->account->name ?? $txn->account_name) : ($txn->contraAccount->name ?? '—');
                $credit = $txn->creditAccountId() === $txn->account_id ? ($txn->account->name ?? $txn->account_name) : ($txn->contraAccount->name ?? '—');
            @endphp
            <tr id="row_{{ $txn->id }}">
                <td>{{ $txn->txn_date ? $txn->txn_date->format('Y-m-d') : '' }}</td>
                <td><b>{{ $debit }}</b></td>
                <td><b>{{ $credit }}</b></td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($txn->type) }}</span></td>
                <td>{{ currency_symbol() }}{{ number_format($txn->amount, 2) }}</td>
                <td>{{ $txn->reference }}</td>
                <td>
                    @if($txn->isAutoPosted())
                        <span class="bullet-badge bullet-badge-info" title="Posted from a {{ $txn->sourceLabel() }} — edit that record instead">{{ $txn->sourceLabel() }}</span>
                    @else
                        <span class="bullet-badge bullet-badge-warning">Manual</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('accounting_update') && ! $txn->isAutoPosted())
                        <a href="{{ route('acc.txn.edit', $txn->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('accounting_delete') && ! $txn->isAutoPosted())
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('acc.txn.delete', $txn->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $txn->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection

@push@endpush
