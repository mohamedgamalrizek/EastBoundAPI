@extends('backend.partials.master')
@section('title') Add Tenant @endsection
@section('maincontent')
<x-page title="Add Tenant" :breadcrumb="['Super Admin','Tenants','Create']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.tenant.store') }}" method="POST">
            @csrf
            @include('saas::saas._tenant-form', ['tenant' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
