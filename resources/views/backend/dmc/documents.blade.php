@extends('backend.partials.master')
@section('title') Trip Documents & Vouchers @endsection
@section('maincontent')
<x-page title="Trip Documents & Vouchers" :breadcrumb="['DMC', 'Documents']">
    <div class="tv-card"><div class="tv-card-body"><table class="table"><thead><tr><th>Trip</th><th>Customer</th><th>Dates</th><th>Status</th><th>Documents</th></tr></thead><tbody>
        @foreach($trips as $trip)<tr><td>#{{ $trip->id }} {{ $trip->title }}</td><td>{{ $trip->customer->name }}</td><td>{{ $trip->starts_on->toDateString() }} — {{ $trip->ends_on->toDateString() }}</td><td>{{ $trip->status }}</td><td><a href="{{ route('dmc.trip-documents', $trip->id) }}">Open document pack</a></td></tr>@endforeach
    </tbody></table>{{ $trips->links() }}</div></div>
</x-page>
@endsection
