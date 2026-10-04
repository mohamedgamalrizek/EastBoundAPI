@extends('backend.partials.master')
@section('title') {{ ___('label.hajj_pilgrim') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Hajj','Pilgrims','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('hajj.pilgrim.store') }}" method="POST">
                        @csrf
                        @include('backend.hajj.pilgrim._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
