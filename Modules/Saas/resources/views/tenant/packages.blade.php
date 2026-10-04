@extends('saas::tenant.layout')
@section('title', 'Packages')
@section('content')
    <h4 class="mb-4">Packages</h4>
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Destination</th><th>Price</th><th>Duration</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $r->id }}</td><td><b>{{ $r->title }}</b></td><td>{{ $r->destination ?? '—' }}</td><td>৳{{ number_format($r->price) }}</td><td>{{ $r->duration_days ? $r->duration_days.' days' : '—' }}</td><td>{{ ucfirst($r->status) }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No packages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
