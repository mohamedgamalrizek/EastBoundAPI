<?php

namespace App\Http\Requests\FlightRoute;

use Illuminate\Validation\Rule;
use App\Repositories\FlightRoute\FlightRouteRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRouteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'origin'           => ['required', 'string', 'max:255'],
            'origin_code'      => ['nullable', 'string', 'max:10'],
            'destination'      => ['required', 'string', 'max:255'],
            'destination_code' => ['nullable', 'string', 'max:10'],
            'airline'          => ['nullable', 'string', 'max:255'],
            'fare'             => ['nullable', 'numeric', 'min:0'],
            'trip_type'        => ['required', Rule::in(FlightRouteRepository::TRIP_TYPES)],
            'is_featured'      => ['nullable', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'status'           => ['required', Rule::in(FlightRouteRepository::STATUSES)],
        ];
    }
}
