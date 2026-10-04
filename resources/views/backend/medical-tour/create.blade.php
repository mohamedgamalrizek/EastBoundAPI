@extends('backend.partials.master')
@section('title') Medical Tourism Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Medical Tourism','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('medical-tour.store') }}" method="POST">
            @csrf
            @include('backend.medical-tour._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
