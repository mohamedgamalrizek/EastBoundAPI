<?php

namespace App\Http\Requests\Visa;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class BookVisaAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'                 => ['required', 'exists:visa_applications,id'],
            'embassy_center'     => ['required', 'string', 'max:150'],
            'appointment_date'   => ['required', 'date'],
            'appointment_time'   => ['required', ],
            'appointment_status' => ['required', Rule::in(['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'])],
            'appointment_notes'  => ['nullable', 'string', 'max:1000'],
        ];
    }
}
