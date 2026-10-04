@extends('backend.partials.master')
@section('title', 'Edit social link')
@section('maincontent')
<x-page title="Edit social link" :breadcrumb="['Settings', 'Social links', 'Edit']"><div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body"><form method="POST" action="{{ route('settings.social-links.update', $item) }}">@csrf @method('PUT') @include('backend.settings.social_links._form')</form></div></div></div></div></x-page>
@endsection
