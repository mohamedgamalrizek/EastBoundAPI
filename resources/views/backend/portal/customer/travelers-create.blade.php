@extends('backend.partials.master')
@section('title') {{ ___('label.add_traveler') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.add_traveler') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.travelers'), ___('label.add')]">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('cust.travelers.store') }}" method="POST">
            @csrf
            @include('backend.portal.customer._travelers-form', ['traveler' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
