@extends('backend.partials.master')
@section('title') Branches Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Branches','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('branch.store') }}" method="POST">
            @csrf
            @include('backend.branch._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
