@extends('backend.partials.master')
@section('title') Event & Conference Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Event & Conference','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('event-tour.store') }}" method="POST">
            @csrf
            @include('backend.event-tour._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
