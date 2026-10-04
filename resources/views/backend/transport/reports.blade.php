@extends('backend.partials.master')
@section('title') Transport Reports @endsection
@section('maincontent')
<x-page title="Transport Reports" :breadcrumb="['Transport','Reports']">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Trips</div><h3 class="mb-0">{{ number_format($totalTrips) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Transport Revenue</div><h3 class="mb-0 text-success">{{ currency_symbol() }}{{ number_format($totalFare) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Transport Modes</div><h3 class="mb-0">{{ $byType->count() }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Avg. Fare / Trip</div><h3 class="mb-0">{{ currency_symbol() }}{{ $totalTrips ? number_format($totalFare / $totalTrips) : 0 }}</h3></div></div></div>
    </div>

    <x-data-table :headers="['Mode','Trips','Total Fare']">
        @foreach($byType as $type => $count)
            <tr>
                <td><b>{{ $type }}</b></td>
                <td>{{ $count }}</td>
                <td>{{ currency_symbol() }}{{ number_format($fareByType[$type] ?? 0) }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
