@extends('backend.partials.master')
@section('title') Campaigns Create @endsection
@section('maincontent')
<x-page title="Create" :breadcrumb="['Campaigns','Create']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('campaign.store') }}" method="POST">
            @csrf
            @include('backend.campaign._form', ['item' => null])
        </form>
    </div></div></div></div>
</x-page>
@endsection
