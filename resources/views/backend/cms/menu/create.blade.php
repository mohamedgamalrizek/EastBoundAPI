@extends('backend.partials.master')
@section('title') {{ ___('label.menu') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="[___('menus.cms'), ___('label.menu'), ___('label.create')]">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.menu.store') }}" method="POST">
                        @csrf
                        @include('backend.cms.menu._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
