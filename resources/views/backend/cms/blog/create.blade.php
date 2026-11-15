@extends('backend.partials.master')
@section('title') {{ 'Magazine article' }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="[___('menus.cms'), 'Magazine', ___('label.create')]">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.blog.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.cms.blog._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
