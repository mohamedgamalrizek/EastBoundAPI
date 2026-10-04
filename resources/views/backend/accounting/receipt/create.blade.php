@extends('backend.partials.master')
@section('title') {{ ___('label.receipt') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Accounting','Receipts','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('acc.receipt.store') }}" method="POST">
                        @csrf
                        @include('backend.accounting.receipt._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
