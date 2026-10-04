<?php

namespace App\Http\Requests\TourSchedule;

use Illuminate\Validation\Rule;
use App\Repositories\TourSchedule\TourScheduleRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreTourScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_id'    => ['nullable', 'exists:packages,id'],
            'package_title' => ['required', 'string', 'max:150'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'seats'         => ['required', 'integer', 'min:0'],
            'booked'        => ['required', 'integer', 'min:0', 'lte:seats'],
            'status'        => ['required', Rule::in(TourScheduleRepository::STATUSES)],
        ];
    }

    public function messages()
    {
        return [
            'booked.lte' => 'Booked seats cannot exceed total seats.',
        ];
    }
}
