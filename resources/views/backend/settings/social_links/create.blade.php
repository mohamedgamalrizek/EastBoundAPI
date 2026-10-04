@extends('backend.partials.master')
@section('title', 'Add social link')
@section('maincontent')
<x-page title="Add social link" :breadcrumb="['Settings', 'Social links', 'Add']"><div class="row"><div class="col-12"><div class="tv-card"><div class="tv-card-body"><form method="POST" action="{{ route('settings.social-links.store') }}">@csrf @include('backend.settings.social_links._form')</form></div></div></div></div></x-page>
@endsection
