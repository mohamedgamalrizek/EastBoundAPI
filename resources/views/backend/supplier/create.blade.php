@extends('backend.partials.master')
@section('title') {{ ___('label.supplier') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Supplier','Suppliers','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('supplier.store') }}" method="POST">
                        @csrf
                        @include('backend.supplier._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
