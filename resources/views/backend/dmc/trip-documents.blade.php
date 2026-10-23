@extends('backend.partials.master')
@section('title') Trip #{{ $trip->id }} Documents @endsection
@section('maincontent')
<x-page :title="'Trip #'.$trip->id.' Documents'" :breadcrumb="['DMC', 'Documents']">
    <x-slot:action><a class="btn btn-primary" href="{{ route('dmc.trip-documents', ['trip' => $trip->id, 'format' => 'pdf']) }}">Download PDF</a></x-slot:action>
    @include('backend.dmc.trip-pack')
</x-page>
@endsection
