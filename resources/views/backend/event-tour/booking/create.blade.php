@extends('backend.partials.master')
@section('title') {{ ___('label.booking') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Events & Tours','Bookings','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('event-tour.booking.store') }}" method="POST">
                        @csrf
                        @include('backend.event-tour.booking._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
