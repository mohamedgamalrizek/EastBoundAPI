@extends('pdf.layout', [
    'documentTitle'     => 'Invoice',
    'documentNo'        => $invoice->invoice_no,
    'documentStatus'    => ucfirst($invoice->status),
    'documentStatusTone' => match($invoice->status) {
        'paid'     => 'paid',
        'refunded' => 'void',
        'overdue'  => 'void',
        'partial'  => 'due',
        default    => 'due',
    },
])

@section('body')
@php
    $paid      = (float) $invoice->paid_amount;
    $refunded  = (float) $invoice->refunded_amount;
    $total     = (float) $invoice->amount;
    // What the customer still owes: refunds give money back, so they do not
    // reduce the debt the invoice records.
    $due       = max(0, round($total - $paid, 2));
    $symbol    = currency_symbol();
@endphp

<table class="facts">
    <tr>
        <td>
            <div class="label">Billed to</div>
            <div class="value">{{ $invoice->customer_name ?: optional($invoice->customer)->name ?: 'Walk-in' }}</div>
            @if(optional($invoice->customer)->phone)
                <div class="brand-meta">{{ $invoice->customer->phone }}</div>
            @endif
            @if(optional($invoice->customer)->email)
                <div class="brand-meta">{{ $invoice->customer->email }}</div>
            @endif
        </td>
        <td>
            <table class="facts">
                <tr>
                    <td><div class="label">Issued</div><div class="value">{{ dateFormat($invoice->issue_date) }}</div></td>
                    <td><div class="label">Due</div><div class="value">{{ $invoice->due_date ? dateFormat($invoice->due_date) : '—' }}</div></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<h2>What this covers</h2>
<table class="grid">
    <thead>
        <tr>
            <th>Description</th>
            <th class="num">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $lineDescription }}</td>
            <td class="num">{{ $symbol }}{{ number_format($total, 2) }}</td>
        </tr>
    </tbody>
</table>

@if($invoice->receipts->isNotEmpty())
    <h2>Payments received</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date</th>
                <th>Method</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->receipts as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_no }}</td>
                    <td>{{ dateFormat($receipt->received_on) }}</td>
                    <td>{{ $receipt->method }}</td>
                    <td class="num">{{ $symbol }}{{ number_format((float) $receipt->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if($invoice->refunds->isNotEmpty())
    <h2>Refunded</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date</th>
                <th>Method</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->refunds as $refund)
                <tr>
                    <td>{{ $refund->reference }}</td>
                    <td>{{ dateFormat($refund->refunded_on) }}</td>
                    <td>{{ $refund->method }}</td>
                    <td class="num">{{ $symbol }}{{ number_format((float) $refund->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<table class="totals">
    <tr>
        <td>Invoice total</td>
        <td class="num">{{ $symbol }}{{ number_format($total, 2) }}</td>
    </tr>
    <tr>
        <td>Paid</td>
        <td class="num">{{ $symbol }}{{ number_format($paid, 2) }}</td>
    </tr>
    @if($refunded > 0)
        <tr>
            <td>Refunded</td>
            <td class="num">{{ $symbol }}{{ number_format($refunded, 2) }}</td>
        </tr>
    @endif
    <tr class="grand">
        <td>Balance due</td>
        <td class="num">{{ $symbol }}{{ number_format($due, 2) }}</td>
    </tr>
</table>

@if($due > 0)
    <div class="note">Please settle the balance by {{ $invoice->due_date ? dateFormat($invoice->due_date) : 'the due date' }}. Quote {{ $invoice->invoice_no }} with your payment.</div>
@endif
@endsection
