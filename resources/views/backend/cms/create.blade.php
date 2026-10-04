@extends('backend.partials.master')
@section('title') {{ ___('label.create') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="[___('menus.cms'), ___('label.pages'), ___('label.create')]">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.store') }}" method="POST">
                        @csrf
                        @include('backend.cms._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
