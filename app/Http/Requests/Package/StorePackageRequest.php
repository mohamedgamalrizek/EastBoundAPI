<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'destination'     => ['required', 'string', 'max:255'],
            'category_id'     => ['required', 'integer', 'exists:package_categories,id'],
            'price'           => ['required', 'numeric', 'min:0'],
            'child_price'     => ['nullable', 'numeric', 'min:0'],
            'single_supplement' => ['nullable', 'numeric', 'min:0'],
            'duration_days'   => ['required', 'integer', 'min:1'],
            'duration_nights' => ['required', 'integer', 'min:0'],
            'status'          => ['required', 'in:active,inactive'],
            'image'           => ['nullable', 'image', 'max:2048'],
            'image_url'       => ['nullable', 'string', 'max:500'],
            'itinerary'                 => ['nullable', 'array', 'max:60'],
            'itinerary.*.day_number'    => ['nullable', 'integer', 'min:1', 'max:365'],
            'itinerary.*.title'         => ['nullable', 'string', 'max:150'],
            'itinerary.*.description'   => ['nullable', 'string', 'max:2000'],
            'description'     => ['nullable', 'string'],
            'inclusions'      => ['nullable', 'string', 'max:3000'],
            'exclusions'      => ['nullable', 'string', 'max:3000'],
        ];
    }
}
