@extends('backend.partials.master')
@section('title') Edit Contract @endsection
@section('maincontent')
<x-page title="Edit Contract" :breadcrumb="['Suppliers','Contracts','Edit']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('sup.contract.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $item->id }}">
            @include('backend.supplier._contract-form')
        </form>
    </div></div></div></div>
</x-page>
@endsection
