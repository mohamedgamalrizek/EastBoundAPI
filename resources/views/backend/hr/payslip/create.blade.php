@extends('backend.partials.master')
@section('title') {{ ___('label.payslip') }} {{ ___('label.add') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['HR','Payslip','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('hr.payslip.store') }}" method="POST">
                        @csrf
                        @include('backend.hr.payslip._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
