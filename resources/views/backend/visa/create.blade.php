@extends('backend.partials.master')
@section('title') {{ ___('label.visa') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Visa','Applications','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('visa.application.store') }}" method="POST">
                        @csrf
                        @include('backend.visa._form', ['application' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
