@extends('backend.partials.master')
@section('title') {{ ___('label.edit_traveler') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit_traveler') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.travelers'), ___('label.edit')]">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('cust.travelers.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $traveler->id }}">
            @include('backend.portal.customer._travelers-form', ['traveler' => $traveler])
        </form>
    </div></div></div></div>
</x-page>
@endsection
