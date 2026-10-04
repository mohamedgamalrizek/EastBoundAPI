@extends('saas::tenant.layout')
@section('title', 'Customers')
@section('content')
    <h4 class="mb-4">Customers</h4>
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Tier</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $r->id }}</td><td><b>{{ $r->name }}</b></td><td>{{ $r->email ?? '—' }}</td><td>{{ $r->phone ?? '—' }}</td><td>{{ $r->tier }}</td><td>{{ ucfirst($r->status) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
