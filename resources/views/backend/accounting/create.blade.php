@extends('backend.partials.master')
@section('title') {{ ___('label.accounting') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Accounting','Chart of Accounts','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('acc.store') }}" method="POST">
                        @csrf
                        @include('backend.accounting._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
