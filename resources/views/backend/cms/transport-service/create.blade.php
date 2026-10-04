@extends('backend.partials.master')
@section('title') Transport Service {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['CMS','Transport Services','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.transport-service.store') }}" method="POST">
                        @csrf
                        @include('backend.cms.transport-service._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
