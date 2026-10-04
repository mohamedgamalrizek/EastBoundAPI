@extends('backend.partials.master')
@section('title') Upload Visa Document @endsection

@section('maincontent')
<x-page title="Upload Visa Document" :breadcrumb="['Visa', 'Documents', 'Create']">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('visa.documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.visa.partials.document-form', ['document' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
