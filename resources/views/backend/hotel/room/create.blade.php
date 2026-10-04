@extends('backend.partials.master')
@section('title') {{ ___('label.room') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Hotel','Rooms','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('hotel.room.store') }}" method="POST">
                        @csrf
                        @include('backend.hotel.room._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
