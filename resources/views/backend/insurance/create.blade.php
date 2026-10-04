@extends('backend.partials.master')
@section('title') Travel Insurance Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Travel Insurance','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('insurance.store') }}" method="POST">
            @csrf
            @include('backend.insurance._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
