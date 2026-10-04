@extends('backend.partials.master')
@section('title') {{ ___('label.invoice') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.invoice') }}" :breadcrumb="['Accounting','Invoices']">

    @if(hasPermission('accounting_create'))
    <x-slot name="action">
        <a href="{{ route('acc.invoice.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.add') }}</span>
        </a>
    </x-slot>
    @endif

    <x-list-analytics
        title="Invoices overview"
        :stats="$analytics['stats']"
        :donut="$analytics['donut']"
        :trend="$analytics['trend']" />

    <x-data-table :headers="['Invoice No','Customer','Issue Date','Due Date','Amount','Paid','Refunded','Due','Status','Action']">
        @foreach($items as $r)
            @php
                $c = match($r->status) {
                    'paid'     => 'success',
                    'overdue'  => 'danger',
                    'refunded' => 'info',
                    default    => 'warning',
                };
                $refundable = round((float) $r->paid_amount - (float) $r->refunded_amount, 2);
            @endphp
            <tr id="row_{{ $r->id }}">
                <td><b>{{ $r->invoice_no }}</b></td>
                <td>{{ $r->customer->name ?? $r->customer_name }}</td>
                <td>{{ $r->issue_date?->format('Y-m-d') }}</td>
                <td>{{ $r->due_date?->format('Y-m-d') }}</td>
                <td>{{ currency_symbol() }}{{ number_format($r->amount, 2) }}</td>
                <td class="text-success">{{ currency_symbol() }}{{ number_format($r->paid_amount, 2) }}</td>
                <td class="{{ $r->refunded_amount > 0 ? 'text-info' : '' }}">{{ currency_symbol() }}{{ number_format($r->refunded_amount, 2) }}</td>
                <td class="{{ $r->dueAmount() > 0 ? 'text-danger' : '' }}">{{ currency_symbol() }}{{ number_format($r->dueAmount(), 2) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($r->status) }}</span></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                        @if(hasPermission('accounting_update') && $refundable > 0)
                        <button type="button" class="btn btn-sm btn-outline-warning" title="Refund"
                                data-toggle="modal" data-target="#refund_{{ $r->id }}">
                            <i class="fa fa-rotate-left"></i> Refund
                        </button>
                        @endif
                        <a href="{{ route('doc.invoice', $r->id) }}" class="btn btn-sm btn-outline-secondary" title="PDF"><i class="fa fa-file-pdf"></i></a>
                        @if(hasPermission('accounting_update'))
                        <a href="{{ route('acc.invoice.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>
                        @endif
                        @if(hasPermission('accounting_delete'))
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('acc.invoice.delete', $r->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $r->id }}" data-title="{{ ___('label.delete') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </td>
            </tr>

            @if(hasPermission('accounting_update') && $refundable > 0)
            {{-- Refunding never edits the invoice: it records a refund against
                 it, which posts Dr Refunds / Cr cash, bank or wallet. --}}
            <div class="modal fade" id="refund_{{ $r->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('acc.invoice.refund', $r->id) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Refund {{ $r->invoice_no }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="label-style-1" for="amount_{{ $r->id }}">Amount</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $refundable }}"
                                       id="amount_{{ $r->id }}" name="amount" class="form-control input-style-1"
                                       value="{{ $refundable }}">
                                <small class="text-muted">Received {{ currency_symbol() }}{{ number_format($r->paid_amount, 2) }}, already refunded {{ currency_symbol() }}{{ number_format($r->refunded_amount, 2) }}.</small>
                            </div>
                            <div class="form-group">
                                <label class="label-style-1" for="method_{{ $r->id }}">Refund to</label>
                                <select id="method_{{ $r->id }}" name="method" class="form-control input-style-1">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="Wallet" @disabled(! $r->customer_id)>Customer wallet</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="label-style-1" for="reason_{{ $r->id }}">Reason</label>
                                <input type="text" id="reason_{{ $r->id }}" name="reason" class="form-control input-style-1" placeholder="Trip cancelled">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="j-td-btn btn-red" data-dismiss="modal">{{ ___('label.cancel') }}</button>
                            <button type="submit" class="j-td-btn">Record refund</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        @endforeach
    </x-data-table>

</x-page>
@endsection
