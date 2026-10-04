<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Validation\Rule;
use App\Repositories\Hotel\HotelRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'              => ['required', 'exists:hotels,id'],
            'name'            => ['required', 'string', 'max:150'],
            'city'            => ['required', 'string', 'max:100'],
            'country'         => ['required', 'string', 'max:100'],
            'category'        => ['required', 'integer', Rule::in(HotelRepository::CATEGORIES)],
            'image'           => ['nullable', 'image', 'max:4096'],
            'image_url'       => ['nullable', 'string', 'max:500'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'is_featured'     => ['nullable', 'boolean'],
            'rooms_count'     => ['required', 'integer', 'min:0'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'status'          => ['required', Rule::in(HotelRepository::STATUSES)],
        ];
    }
}
