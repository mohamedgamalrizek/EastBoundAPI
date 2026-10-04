@extends('backend.partials.master')
@section('title') Campaigns Edit @endsection
@section('maincontent')
<x-page title="Edit" :breadcrumb="['Campaigns','Edit']">
    <div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body">
        <form action="{{ route('campaign.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $item->id }}">
            @include('backend.campaign._form', ['item' => $item])
        </form>
    </div></div></div></div>
</x-page>
@endsection
