@extends('backend.partials.master')
@section('title') Corporate Travel Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Corporate Travel','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('corporate-travel.store') }}" method="POST">
            @csrf
            @include('backend.corporate-travel._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
