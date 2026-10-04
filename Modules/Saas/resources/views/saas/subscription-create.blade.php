@extends('backend.partials.master')
@section('title') Add Subscription @endsection
@section('maincontent')
<x-page title="Add Subscription" :breadcrumb="['Super Admin','Subscriptions','Create']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.subscription.store') }}" method="POST">
            @csrf
            @include('saas::saas._subscription-form', ['subscription' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
