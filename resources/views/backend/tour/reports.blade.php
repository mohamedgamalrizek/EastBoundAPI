@extends('backend.partials.master')
@section('title') Package Reports @endsection
@section('maincontent')
<x-page title="Package Reports" :breadcrumb="['Tour','Reports']">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Packages</div><h3 class="mb-0">{{ $totalPackages }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Categories</div><h3 class="mb-0">{{ $totalCategories }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Scheduled Departures</div><h3 class="mb-0">{{ $totalSchedules }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Tour Guides</div><h3 class="mb-0">{{ $totalGuides }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Seats</div><h3 class="mb-0">{{ $totalSeats }}</h3></div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Seats Booked</div><h3 class="mb-0 text-success">{{ $totalBooked }}</h3></div></div></div>
        <div class="col-md-4 col-12"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Occupancy</div><h3 class="mb-0">{{ $occupancy }}%</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Packages by Category</h4></div>
                <div class="tv-card-body">
                    <x-data-table :headers="['Category','Packages']" :card="false">
                        @foreach($byCategory as $cat => $count)
                            <tr>
                                <td>{{ $cat ?: 'Uncategorised' }}</td>
                                <td><b>{{ $count }}</b></td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">Schedules by Status</h4></div>
                <div class="tv-card-body">
                    <x-data-table :headers="['Status','Departures']" :card="false">
                        @foreach($scheduleByStatus as $st => $count)
                            @php $cls = $st === 'open' ? 'success' : ($st === 'full' ? 'danger' : 'secondary'); @endphp
                            <tr>
                                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($st) }}</span></td>
                                <td><b>{{ $count }}</b></td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">Top Departures by Bookings</h4></div>
                <div class="tv-card-body">
                    <x-data-table :headers="['Package','Departure','Booked / Seats','Status']" :card="false">
                        @foreach($topSchedules as $s)
                            @php $cls = $s->status === 'open' ? 'success' : ($s->status === 'full' ? 'danger' : 'secondary'); @endphp
                            <tr>
                                <td><b>{{ $s->package_title }}</b></td>
                                <td>{{ $s->start_date?->format('d M Y') }}</td>
                                <td>{{ $s->booked }} / {{ $s->seats }}</td>
                                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ ucfirst($s->status) }}</span></td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

</x-page>
@endsection
