@extends('backend.partials.master')
@section('title') {{ ___('label.commission') }} {{ ___('label.edit') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit') }}" :breadcrumb="['Agent','Commission','Edit']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('agent.commission.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        @include('backend.agent.commission._form', ['item' => $item])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
