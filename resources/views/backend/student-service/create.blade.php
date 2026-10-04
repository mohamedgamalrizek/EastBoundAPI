@extends('backend.partials.master')
@section('title') Student Consultancy Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Student Consultancy','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('student-service.store') }}" method="POST">
            @csrf
            @include('backend.student-service._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
