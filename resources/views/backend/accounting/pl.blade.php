@extends('backend.partials.master')
@section('title') Profit & Loss @endsection
@section('maincontent')
<x-page title="Profit & Loss" :breadcrumb="['Accounting','Profit & Loss']">

@include('backend.accounting.partials._filters')

<div class="row">
    <div class="col-lg-8">
        <div class="tv-card">
            <div class="tv-card-body">
                <div class="text-muted mb-3 tv-text-sm">
                    {{ $from ? \Illuminate\Support\Carbon::parse($from)->format('d M Y') : 'Since the first entry' }}
                    &rarr;
                    {{ $to ? \Illuminate\Support\Carbon::parse($to)->format('d M Y') : 'today' }}
                </div>

                <h5 class="text-success mb-4">Income</h5>
                <table class="table">
                    <tbody>
                        @forelse($incomeRows as $r)
                            <tr>
                                <td>{{ $r['code'] }} — {{ $r['name'] }}</td>
                                <td class="text-right">{{ currency_symbol() }}{{ number_format($r['net'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">No income in this period.</td></tr>
                        @endforelse
                        <tr class="fw-700">
                            <td><b>Total Income</b></td>
                            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalIncome, 2) }}</b></td>
                        </tr>
                    </tbody>
                </table>

                <h5 class="text-danger mt-3 mb-4 pt-4">Expenses</h5>
                <table class="table">
                    <tbody>
                        @forelse($expenseRows as $r)
                            <tr>
                                <td>{{ $r['code'] }} — {{ $r['name'] }}</td>
                                <td class="text-right">{{ currency_symbol() }}{{ number_format($r['net'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">No expenses in this period.</td></tr>
                        @endforelse
                        <tr class="fw-700">
                            <td><b>Total Expenses</b></td>
                            <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($totalExpense, 2) }}</b></td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between h4 mt-4 pt-4">
                    <b>Net Profit</b>
                    <b class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ currency_symbol() }}{{ number_format($netProfit, 2) }}</b>
                </div>
            </div>
        </div>
    </div>
</div>
</x-page>
@endsection
