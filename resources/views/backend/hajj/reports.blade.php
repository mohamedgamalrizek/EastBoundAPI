@extends('backend.partials.master')
@section('title') Hajj & Umrah Reports @endsection
@section('maincontent')
<x-page title="Reports" :breadcrumb="['Hajj & Umrah','Reports']">

    <div class="row">
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Total Pilgrims</div><h3 class="mb-0">{{ $totalPilgrims }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Packages</div><h3 class="mb-0">{{ $totalPackages }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Groups</div><h3 class="mb-0">{{ $totalGroups }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">Docs Verified</div><h3 class="mb-0 text-success">{{ $docsVerified }} / {{ $totalPilgrims }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-data-table :headers="['Package','Pilgrims']">
                @foreach($byPackage as $package => $total)
                    <tr><td>{{ $package }}</td><td>{{ $total }}</td></tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-md-6">
            <x-data-table :headers="['Status','Pilgrims']">
                @foreach($byStatus as $status => $total)
                    @php $c = $status === 'Confirmed' ? 'success' : ($status === 'Pending' ? 'danger' : 'warning'); @endphp
                    <tr>
                        <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $status }}</span></td>
                        <td>{{ $total }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

</x-page>
@endsection
