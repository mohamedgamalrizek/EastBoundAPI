@extends('saas::tenant.layout')
@section('title', 'Dashboard')
@section('content')
    <h4 class="mb-4">Welcome back 👋</h4>
    <div class="row">
        <div class="col-md-3 col-6 mb-4"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:.82rem">Customers</div><div class="kpi">{{ number_format($customers) }}</div></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:.82rem">Packages</div><div class="kpi">{{ number_format($packages) }}</div></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:.82rem">Bookings</div><div class="kpi">{{ number_format($bookings) }}</div></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:.82rem">Revenue</div><div class="kpi text-success">৳{{ number_format($revenue) }}</div></div></div></div>
    </div>
    <div class="card"><div class="card-header"><b>Recent Bookings</b></div><div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Customer</th><th>Travel date</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recent as $b)
                    <tr><td>{{ $b->id }}</td><td>{{ $b->customer_name ?? '—' }}</td><td>{{ $b->travel_date ?? '—' }}</td><td>৳{{ number_format($b->amount) }}</td><td>{{ ucfirst($b->status) }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No bookings yet — your fresh workspace is ready to fill.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
