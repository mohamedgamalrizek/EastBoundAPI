@extends('backend.partials.master')
@section('title') {{ ___('label.create') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['CMS','Testimonials','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.testimonial.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.cms.testimonial._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
