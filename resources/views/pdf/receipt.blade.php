@extends('pdf.layout', [
    'documentTitle'      => 'Receipt',
    'documentNo'         => $receipt->receipt_no,
    'documentStatus'     => 'Received',
    'documentStatusTone' => 'paid',
])

@section('body')
@php
    $symbol  = currency_symbol();
    $invoice = $receipt->invoice;
@endphp

<table class="facts">
    <tr>
        <td>
            <div class="label">Received from</div>
            <div class="value">{{ $receipt->customer_name ?: optional($receipt->customer)->name ?: 'Walk-in' }}</div>
        </td>
        <td>
            <table class="facts">
                <tr>
                    <td><div class="label">Date</div><div class="value">{{ dateFormat($receipt->received_on) }}</div></td>
                    <td><div class="label">Method</div><div class="value">{{ $receipt->method }}</div></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<h2>Payment</h2>
<table class="grid">
    <thead>
        <tr>
            <th>Against invoice</th>
            <th>Reference</th>
            <th class="num">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $invoice?->invoice_no ?: '—' }}</td>
            <td>{{ $receipt->reference ?: '—' }}</td>
            <td class="num">{{ $symbol }}{{ number_format((float) $receipt->amount, 2) }}</td>
        </tr>
    </tbody>
</table>

@if($invoice)
    @php
        $due = max(0, round((float) $invoice->amount - (float) $invoice->paid_amount, 2));
    @endphp
    <table class="totals">
        <tr>
            <td>Invoice total</td>
            <td class="num">{{ $symbol }}{{ number_format((float) $invoice->amount, 2) }}</td>
        </tr>
        <tr>
            <td>Paid to date</td>
            <td class="num">{{ $symbol }}{{ number_format((float) $invoice->paid_amount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Balance due</td>
            <td class="num">{{ $symbol }}{{ number_format($due, 2) }}</td>
        </tr>
    </table>
@endif

<div class="note">This receipt confirms the amount above was received. Keep it with your travel documents.</div>
@endsection
