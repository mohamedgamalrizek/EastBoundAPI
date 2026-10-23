@extends('backend.partials.master')
@section('title') Lead Languages & Websites @endsection
@section('maincontent')
<x-page title="Lead Languages & Websites" :breadcrumb="['Reports', 'Lead Languages & Websites']">
    @include('backend.reports._filterbar')

    <div class="tv-card mb-4"><div class="tv-card-body">
        <p>Lead counts and opportunity values use the lead creation date. Booked revenue uses the booking creation date and the originating lead's website and language.</p>
        <p class="mb-0">Revenue includes confirmed or paid package and hotel bookings, and confirmed or reissued standalone flights. Cancelled, refunded and pending bookings are excluded. This is booked revenue, not cash collected. Flights included in a package booking are counted through that package only.</p>
    </div></div>

    <div class="row mb-4">
        @foreach(['leads' => 'Leads', 'won' => 'Won Leads', 'bookings' => 'Attributed Bookings', 'revenue' => 'Attributed Booked Revenue'] as $metric => $label)
            <div class="col-md-3 col-6 mb-3"><div class="tv-card"><div class="tv-card-body">
                <div class="text-muted">{{ $label }}</div>
                <h3>{{ $metric === 'revenue' ? currency_symbol() : '' }}{{ number_format($totals[$metric], $metric === 'revenue' ? 2 : 0) }}</h3>
            </div></div></div>
        @endforeach
    </div>

    <p class="text-muted">Booked revenue without an originating lead (all websites/languages in the selected period): {{ currency_symbol() }}{{ number_format($unattributedRevenue, 2) }}. Older bookings need an explicit lead link before their revenue can be attributed. Missing website/language values appear as Unspecified.</p>

    @foreach(['byLanguage' => 'By Language', 'byWebsite' => 'By Website'] as $dataset => $heading)
        <h4 class="title-site mb-3">{{ $heading }}</h4>
        <x-data-table :headers="[$heading, 'Leads', 'Won', 'Conversion %', 'Potential Value', 'Won Lead Value', 'Bookings', 'Booked Revenue']" :order="'[[7,&quot;desc&quot;]]'">
            @foreach(${$dataset} as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ number_format($row['leads']) }}</td>
                    <td>{{ number_format($row['won']) }}</td>
                    <td>{{ $row['leads'] ? number_format($row['won'] / $row['leads'] * 100, 1) : '0.0' }}%</td>
                    <td>{{ currency_symbol() }}{{ number_format($row['potential_value'], 2) }}</td>
                    <td>{{ currency_symbol() }}{{ number_format($row['won_value'], 2) }}</td>
                    <td>{{ number_format($row['bookings']) }}</td>
                    <td>{{ currency_symbol() }}{{ number_format($row['revenue'], 2) }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @endforeach

    <h4 class="title-site mb-3 mt-4">Website × Language</h4>
    <x-data-table :headers="['Website', 'Language', 'Leads', 'Won', 'Potential Value', 'Won Lead Value', 'Bookings', 'Booked Revenue']" :order="'[[7,&quot;desc&quot;]]'">
        @foreach($matrix as $row)
            <tr>
                <td>{{ $row->website }}</td><td>{{ $row->language }}</td>
                <td>{{ number_format($row->leads) }}</td><td>{{ number_format($row->won) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($row->potential_value, 2) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($row->won_value, 2) }}</td>
                <td>{{ number_format($row->bookings) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($row->revenue, 2) }}</td>
            </tr>
        @endforeach
    </x-data-table>
</x-page>
@endsection
