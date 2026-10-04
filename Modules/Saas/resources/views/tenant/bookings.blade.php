@extends('saas::tenant.layout')
@section('title', 'Bookings')
@section('content')
    <h4 class="mb-4">Bookings</h4>
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Customer</th><th>Travel date</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $r->id }}</td><td>{{ $r->customer_name ?? '—' }}</td><td>{{ $r->travel_date ?? '—' }}</td><td>৳{{ number_format($r->amount) }}</td><td>{{ ucfirst($r->status) }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
