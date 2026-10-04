@extends('backend.partials.master')
@section('title') Coupons Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Coupons','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('coupon.store') }}" method="POST">
            @csrf
            @include('backend.coupon._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
