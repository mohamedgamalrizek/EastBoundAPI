@extends('backend.partials.master')
@section('title') Add Contract @endsection
@section('maincontent')
<x-page title="Add Contract" :breadcrumb="['Suppliers','Contracts','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('sup.contract.store') }}" method="POST">
            @csrf
            @include('backend.supplier._contract-form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
