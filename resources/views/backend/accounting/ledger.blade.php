@extends('backend.partials.master')
@section('title') Ledger @endsection
@section('maincontent')
<x-page title="Ledger" :breadcrumb="['Accounting','Ledger']">

@include('backend.accounting.partials._filters', ['accounts' => $accounts, 'selected' => $selected])

@forelse($groups as $g)
    <div class="tv-card mb-4">
        <div class="tv-card-head d-flex justify-content-between align-items-center">
            <h4 class="title-site mb-0">{{ $g['account']->code }} — {{ $g['account']->name }}</h4>
            <span class="bullet-badge bullet-badge-info">{{ $g['account']->type }}</span>
        </div>
        <div class="tv-card-body table-responsive">
            <table class="table table-hover">
                <thead class="bg">
                    <tr>
                        <th>Date</th><th>Particulars</th><th>Against</th><th>Reference</th>
                        <th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-muted">
                        <td colspan="6"><i>Opening balance</i></td>
                        <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($g['opening'], 2) }}</b></td>
                    </tr>
                    @foreach($g['rows'] as $row)
                        <tr>
                            <td>{{ $row['txn']->txn_date?->format('Y-m-d') }}</td>
                            <td>{{ $row['txn']->description }}</td>
                            <td>{{ $row['against'] }}</td>
                            <td>{{ $row['txn']->reference }}</td>
                            <td class="text-right">{{ $row['debit'] ? currency_symbol().number_format($row['debit'], 2) : '—' }}</td>
                            <td class="text-right">{{ $row['credit'] ? currency_symbol().number_format($row['credit'], 2) : '—' }}</td>
                            <td class="text-right">{{ currency_symbol() }}{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-700">
                        <td colspan="4"><b>Closing</b></td>
                        <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($g['debit'], 2) }}</b></td>
                        <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($g['credit'], 2) }}</b></td>
                        <td class="text-right"><b>{{ currency_symbol() }}{{ number_format($g['closing'], 2) }}</b></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="tv-card"><div class="tv-card-body text-center text-muted py-5">
        {{ ___('alert.no_data_available') }}
    </div></div>
@endforelse

</x-page>
@endsection
