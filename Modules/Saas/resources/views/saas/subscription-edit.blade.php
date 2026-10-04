@extends('backend.partials.master')
@section('title') Edit Subscription @endsection
@section('maincontent')
<x-page title="Edit Subscription" :breadcrumb="['Super Admin','Subscriptions','Edit']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.subscription.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $subscription->id }}">
            @include('saas::saas._subscription-form', ['subscription' => $subscription])
        </form>
    </div></div></div></div>
</x-page>
@endsection
