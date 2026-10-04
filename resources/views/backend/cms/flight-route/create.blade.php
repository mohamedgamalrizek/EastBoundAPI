@extends('backend.partials.master')
@section('title') Flight Route {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['CMS','Flight Routes','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.flight-route.store') }}" method="POST">
                        @csrf
                        @include('backend.cms.flight-route._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
