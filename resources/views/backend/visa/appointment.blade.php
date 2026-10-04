@extends('backend.partials.master')
@section('title') Embassy Appointment @endsection

@section('maincontent')
<x-page title="Embassy Appointment" :breadcrumb="['Visa', 'Embassy Appointment']">
    @if(hasPermission('visa_update'))
        <x-slot:action>
            <a href="{{ route('visa.appointment.create') }}"
                class="j-td-btn {{ !$canBookAppointment ? 'disabled' : '' }}"
                @if(!$canBookAppointment) aria-disabled="true" onclick="return false;" @endif>
                <i class="fa fa-calendar-plus mr-1"></i> <span>Book Appointment</span>
            </a>
        </x-slot:action>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($applications->isEmpty())
                        <div class="alert alert-warning">
                            Create a visa application before booking an appointment.
                        </div>
                    @elseif(!$canBookAppointment)
                        <div class="alert alert-info">
                            Every visa application already has an appointment.
                        </div>
                    @endif

                    <div class="table-responsive visa-crud-table-wrap">
                        <table class="table table-responsive-sm">
                            <thead class="bg">
                                <tr>
                                    <th>Application</th>
                                    <th>Applicant</th>
                                    <th>Embassy / Center</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $appointment)
                                    @php
                                        $statusClass = match($appointment->appointment_status) {
                                            'Confirmed', 'Completed' => 'success',
                                            'Cancelled' => 'danger',
                                            'Rescheduled' => 'info',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <tr id="appointment_row_{{ $appointment->id }}">
                                        <td>{{ $appointment->application_no }}</td>
                                        <td>{{ $appointment->applicant_name }}</td>
                                        <td>{{ $appointment->embassy_center ?: '—' }}</td>
                                        <td>{{ optional($appointment->appointment_date)->format('d M Y') }}</td>
                                        <td>
                                            {{ $appointment->appointment_time
                                                ? \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('h:i A')
                                                : '—' }}
                                        </td>
                                        <td>
                                            <span class="bullet-badge bullet-badge-{{ $statusClass }}">
                                                {{ $appointment->appointment_status ?: 'Pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend be-addon">
                                                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                                        @if(hasPermission('visa_update'))
                                                            <a href="{{ route('visa.appointment.edit', $appointment->id) }}"
                                                                class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                        @endif
                                                        @if(hasPermission('visa_delete'))
                                                            <a class="btn btn-sm btn-outline-danger"
                                                                href="{{ route('visa.appointment.delete', $appointment->id) }}"
                                                                onclick="tryDelete(event)"
                                                                data-remove-id="appointment_row_{{ $appointment->id }}"
                                                                data-title="Delete appointment?"
                                                                data-text="The appointment details will be removed from this visa application."
                                                                data-confirm-button-text="Delete"
                                                                data-cancel-button-text="Cancel"><i class="fa fa-trash"></i></a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-nodata-found :colspan="7" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection

@push@endpush
