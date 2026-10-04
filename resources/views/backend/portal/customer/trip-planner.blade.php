@extends('backend.partials.master')
@section('title') AI Trip Planner @endsection
@section('maincontent')
<x-page title="AI Trip Planner" :breadcrumb="['Customer Portal','AI Trip Planner']">
<div class="tv-card mb-4"><div class="tv-card-body"><form method="post" action="{{ route('cust.trip-planner.generate') }}" class="row g-3">@csrf
<div class="col-md-4"><label>Destination</label><input class="form-control" name="destination" value="{{ old('destination') }}" required></div>
<div class="col-md-3"><label>Start date</label><input type="date" class="form-control" name="start_date" min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" required></div>
<div class="col-md-2"><label>Days</label><input type="number" min="1" max="30" class="form-control" name="days" value="{{ old('days',5) }}" required></div>
<div class="col-md-3"><label>Budget</label><input class="form-control" name="budget" value="{{ old('budget') }}"></div>
<div class="col-12"><label>Interests</label><input class="form-control" name="interests" value="{{ old('interests') }}" placeholder="food, nature, shopping"></div>
<div class="col-12"><button class="btn btn-primary">Generate itinerary</button></div></form></div></div>
@foreach($plans as $item)<div class="tv-card mb-3"><div class="tv-card-body"><h4>{{ $item->destination }} · {{ $item->days }} days</h4>@foreach($item->plan['days'] ?? [] as $day)<div class="border-top py-3"><b>Day {{ $day['day'] ?? $loop->iteration }}: {{ $day['title'] ?? '' }}</b><ul>@foreach($day['activities'] ?? [] as $activity)<li>{{ $activity }}</li>@endforeach</ul><small>{{ $day['tips'] ?? '' }}</small></div>@endforeach</div></div>@endforeach
</x-page>@endsection
