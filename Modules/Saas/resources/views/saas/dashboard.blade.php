@extends('backend.partials.master')
@section('title') SaaS Dashboard @endsection
@section('maincontent')
<x-page title="SaaS Dashboard" :breadcrumb="['Super Admin','Dashboard']">

    <div class="row">
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Tenants</div><h3 class="mb-0 text-success tv-fw-700">{{ $tenantCount }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Active Tenants</div><h3 class="mb-0 tv-fw-700">{{ $activeTenants }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">MRR</div><h3 class="mb-0 text-success tv-fw-700">৳{{ number_format($mrr) }}</h3></div></div></div>
        <div class="col-md-3 col-6"><div class="card"><div class="card-body"><div class="text-muted tv-text-xs">Active Subs</div><h3 class="mb-0 tv-fw-700">{{ $activeSubs }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h4 class="title-site mb-0">Recent Tenants</h4></div>
                <div class="card-body">
                    <x-data-table :headers="['Tenant','Plan','Status','Created']" :pageLength="5" :card="false">
                        @foreach($recentTenants as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ optional($tenant->plan)->name ?? '—' }}</td>
                                <td>
                                    @php $s = $tenant->status; @endphp
                                    <span class="bullet-badge bullet-badge-{{ $s === 'active' ? 'success' : ($s === 'suspended' ? 'danger' : 'warning') }}">{{ ucfirst($s) }}</span>
                                </td>
                                <td>{{ $tenant->created_at?->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

</x-page>
@endsection
