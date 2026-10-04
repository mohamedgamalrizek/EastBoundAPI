@extends('backend.partials.master')
@section('title') {{ ___('label.traveler') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Customer','Travelers','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('customer.traveler.store') }}" method="POST">
                        @csrf
                        @include('backend.customer.traveler._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
