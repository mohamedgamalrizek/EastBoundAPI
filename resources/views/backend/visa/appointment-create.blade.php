@extends('backend.partials.master')
@section('title') Book Embassy Appointment @endsection

@section('maincontent')
<x-page title="Book Embassy Appointment" :breadcrumb="['Visa', 'Embassy Appointment', 'Create']">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('visa.appointment.store') }}" method="POST">
                        @csrf
                        @include('backend.visa.partials.appointment-form', ['selectedApplication' => null])
                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">Save Appointment</button>
                                <a href="{{ route('visa.appointment') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
