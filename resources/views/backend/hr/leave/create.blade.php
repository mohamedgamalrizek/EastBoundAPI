@extends('backend.partials.master')
@section('title') {{ ___('label.leave') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['HR','Leave','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('hr.leave.store') }}" method="POST">
                        @csrf
                        @include('backend.hr.leave._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
