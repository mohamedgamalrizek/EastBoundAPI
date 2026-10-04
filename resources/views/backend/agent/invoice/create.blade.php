@extends('backend.partials.master')
@section('title') {{ ___('label.invoice') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Agent','Invoices','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('agent.invoice.store') }}" method="POST">
                        @csrf
                        @include('backend.agent.invoice._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
