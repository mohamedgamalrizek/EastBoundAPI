@extends('backend.partials.master')
@section('title') {{ ___('label.hotel') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Hotel','Hotels','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('hotel.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.hotel._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
