@extends('backend.partials.master')
@section('title') Edit Plan @endsection
@section('maincontent')
<x-page title="Edit Plan" :breadcrumb="['Super Admin','Plans','Edit']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.plan.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $plan->id }}">
            @include('saas::saas._plan-form', ['plan' => $plan])
        </form>
    </div></div></div></div>
</x-page>
@endsection
