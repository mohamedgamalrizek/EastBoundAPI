{{-- Cash / bank book body. Expects the repository payload (accounts, rows,
     opening, closing, totalIn, totalOut, from, to) plus the labels the calling
     page sets: $heading, $crumb, $inLabel, $outLabel, $empty. --}}
<x-page :title="$heading" :breadcrumb="['Accounting', $crumb]">

@include('backend.accounting.partials._filters')

<div class="row">
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
        <div class="text-muted tv-text-xs">Opening</div>
        <h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($opening, 2) }}</h3>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
        <div class="text-muted tv-text-xs">{{ $inLabel }}s</div>
        <h3 class="mb-0 text-success tv-fw-700">{{ currency_symbol() }}{{ number_format($totalIn, 2) }}</h3>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
        <div class="text-muted tv-text-xs">{{ $outLabel }}s</div>
        <h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($totalOut, 2) }}</h3>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body">
        <div class="text-muted tv-text-xs">Closing</div>
        <h3 class="mb-0 tv-fw-700">{{ currency_symbol() }}{{ number_format($closing, 2) }}</h3>
    </div></div></div>
</div>

@if($accounts->isEmpty())
    <div class="alert alert-warning">{{ $empty }}</div>
@else
    <div class="text-muted mb-3 tv-text-sm">
        Accounts: {{ $accounts->pluck('name')->implode(', ') }}
    </div>

    <x-data-table :headers="['Date','Particulars','Against','Reference',$inLabel,$outLabel,'Balance']" order="[[0,'asc']]">
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['txn']->txn_date?->format('Y-m-d') }}</td>
                <td>{{ $row['txn']->description }}</td>
                <td>{{ $row['against'] }}</td>
                <td>{{ $row['txn']->reference }}</td>
                <td class="text-success">{{ $row['debit'] ? currency_symbol().number_format($row['debit'], 2) : '—' }}</td>
                <td class="text-danger">{{ $row['credit'] ? currency_symbol().number_format($row['credit'], 2) : '—' }}</td>
                <td><b>{{ currency_symbol() }}{{ number_format($row['balance'], 2) }}</b></td>
            </tr>
        @endforeach
    </x-data-table>
@endif

</x-page>
