@extends('backend.partials.master')
@section('title') Trip Readiness Alerts @endsection
@section('maincontent')
<x-page title="Automatic Trip Readiness Alerts" :breadcrumb="['DMC', 'Operations']">
    <p class="text-muted">Readiness checks generate internal notifications and assigned tasks at seven days, one day and departure.</p>
    <div class="tv-card"><div class="tv-card-body"><table class="table"><thead><tr><th>Trip</th><th>Check</th><th>Due</th><th>Task</th><th>Status</th></tr></thead><tbody>
        @foreach($alerts as $alert)<tr><td>#{{ $alert->trip_id }} {{ $alert->trip->title }}</td><td>{{ $alert->kind }}</td><td>{{ $alert->trip->starts_on->toDateString() }}</td><td>{{ $alert->task?->title }}</td><td>{{ $alert->task?->status }}</td></tr>@endforeach
    </tbody></table>{{ $alerts->links() }}</div></div>
</x-page>
@endsection
