@extends('backend.partials.master')
@section('title') {{ ___('label.gallery') }} {{ ___('label.edit') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit') }}" :breadcrumb="['CMS','Gallery','Edit']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.gallery.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        @include('backend.cms.gallery._form', ['item' => $item])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
