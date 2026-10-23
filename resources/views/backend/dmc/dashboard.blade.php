@extends('backend.partials.master')
@section('title') Department Dashboard @endsection
@section('maincontent')
<x-page title="Department Dashboard" :breadcrumb="['Dashboard']">
    <p>Welcome, {{ auth()->user()->name }}. {{ auth()->user()->role?->name }}</p>
    <div class="row">@foreach($metrics as $label => $value)<div class="col-md-4 mb-4"><div class="tv-card"><div class="tv-card-body"><div class="text-muted">{{ $label }}</div><h3>{{ number_format($value, 2) }}</h3></div></div></div>@endforeach</div>
</x-page>
@endsection
