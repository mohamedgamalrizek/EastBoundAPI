@extends('backend.partials.master')
@section('title') {{ ___('label.edit_passport') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit_passport') }}" :breadcrumb="[___('permissions.customer_portal'), ___('label.passport'), ___('label.edit')]">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('cust.passport.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $passport->id }}">
            @include('backend.portal.customer._passport-form', ['passport' => $passport])
        </form>
    </div></div></div></div>
</x-page>
@endsection
