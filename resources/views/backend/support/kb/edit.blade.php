@extends('backend.partials.master')
@section('title') {{ ___('label.knowledge_base') }} {{ ___('label.edit') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit') }}" :breadcrumb="['Support','Knowledge Base','Edit']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('support.kb.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        @include('backend.support.kb._form', ['item' => $item])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
