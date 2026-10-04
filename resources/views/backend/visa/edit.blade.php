@extends('backend.partials.master')
@section('title') {{ ___('label.visa') }} {{ ___('label.edit') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit') }}" :breadcrumb="['Visa','Applications','Edit']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('visa.application.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $application->id }}">
                        @include('backend.visa._form', ['application' => $application])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
