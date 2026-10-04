@extends('backend.partials.master')
@section('title') {{ ___('label.add_passport') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.add_passport') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.passport'), ___('label.add')]">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('cust.passport.store') }}" method="POST">
            @csrf
            @include('backend.portal.customer._passport-form', ['passport' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
