@extends('backend.partials.master')
@section('title') Supplier Reports @endsection
@section('maincontent')
<x-page title="Supplier Reports" :breadcrumb="['Suppliers','Reports']">

    <div class="row">
        <div class="col-md-3 col-6">
            <div class="tv-card"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Total Suppliers</div>
                <h3 class="mb-0 tv-fw-700">{{ $totalCount }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="tv-card"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Active Suppliers</div>
                <h3 class="mb-0 text-success tv-fw-700">{{ $activeCount }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="tv-card"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Total Payables</div>
                <h3 class="mb-0 text-danger tv-fw-700">{{ currency_symbol() }}{{ number_format($totalBalance) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="tv-card"><div class="tv-card-body">
                <div class="text-muted tv-text-xs">Supplier Types</div>
                <h3 class="mb-0 tv-fw-700">{{ $byType->count() }}</h3>
            </div></div>
        </div>
    </div>

    <x-data-table :headers="['Type','Suppliers','Total Balance']">
        @foreach($byType as $type => $count)
            <tr>
                <td><b>{{ $type }}</b></td>
                <td>{{ $count }}</td>
                <td>{{ currency_symbol() }}{{ number_format($balanceByType[$type] ?? 0) }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
