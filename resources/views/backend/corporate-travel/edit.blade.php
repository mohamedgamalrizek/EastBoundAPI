@extends('backend.partials.master')
@section('title') Corporate Travel Edit @endsection
@section('maincontent')
<x-page title="Edit" :breadcrumb="['Corporate Travel','Edit']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('corporate-travel.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $item->id }}">
            @include('backend.corporate-travel._form', ['item' => $item])
        </form>
    </div></div></div></div>
</x-page>
@endsection
