@extends('backend.partials.master')
@section('title') {{ ___('label.activity') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['CRM','Activities','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('crm.activity.store') }}" method="POST">
                        @csrf
                        @include('backend.crm.activity._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
