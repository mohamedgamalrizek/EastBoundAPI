@extends('backend.partials.master')
@section('title') Edit Embassy Appointment @endsection

@section('maincontent')
<x-page title="Edit Embassy Appointment" :breadcrumb="['Visa', 'Embassy Appointment', 'Edit']">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('visa.appointment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('backend.visa.partials.appointment-form', ['selectedApplication' => $appointment])
                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">Save Changes</button>
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
