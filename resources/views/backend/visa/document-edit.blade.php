@extends('backend.partials.master')
@section('title') Edit Visa Document @endsection

@section('maincontent')
<x-page title="Edit Visa Document" :breadcrumb="['Visa', 'Documents', 'Edit']">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('visa.documents.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $document->id }}">
                        @include('backend.visa.partials.document-form', ['document' => $document])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
