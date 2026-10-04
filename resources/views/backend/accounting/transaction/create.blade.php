@extends('backend.partials.master')
@section('title') {{ ___('label.transaction') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Accounting','Transactions','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('acc.txn.store') }}" method="POST">
                        @csrf
                        @include('backend.accounting.transaction._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
