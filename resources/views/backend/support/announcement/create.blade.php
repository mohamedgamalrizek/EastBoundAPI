@extends('backend.partials.master')
@section('title') {{ ___('label.announcements') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Support','Announcements','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('support.announcement.store') }}" method="POST">
                        @csrf
                        @include('backend.support.announcement._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
