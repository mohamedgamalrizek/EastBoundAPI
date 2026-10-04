@extends('backend.partials.master')
@section('title') Edit Tenant @endsection
@section('maincontent')
<x-page title="Edit Tenant" :breadcrumb="['Super Admin','Tenants','Edit']">
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <form action="{{ route('saas.tenant.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $tenant->id }}">
            @include('saas::saas._tenant-form', ['tenant' => $tenant])
        </form>
    </div></div></div></div>
</x-page>
@endsection
