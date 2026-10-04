@extends('backend.partials.master')
@section('title') Add Plan @endsection
@section('maincontent')
<x-page title="Add Plan" :breadcrumb="['Super Admin','Plans','Create']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.plan.store') }}" method="POST">
            @csrf
            @include('saas::saas._plan-form', ['plan' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
