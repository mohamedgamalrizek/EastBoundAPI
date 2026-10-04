@extends('backend.partials.master')
@section('title') Vehicle Category {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Transport','Vehicle Categories','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('transport.vehicle-category.store') }}" method="POST">
                        @csrf
                        @include('backend.vehicle-category._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
