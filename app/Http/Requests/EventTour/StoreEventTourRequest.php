<?php

namespace App\Http\Requests\EventTour;

use Illuminate\Validation\Rule;
use App\Repositories\EventTour\EventTourRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventTourRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(EventTourRepository::TYPE)],
            'location' => ['nullable', 'string', 'max:191'],
            'event_date' => ['nullable', 'date'],
            'seats' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(EventTourRepository::STATUS)],
        ];
    }
}
