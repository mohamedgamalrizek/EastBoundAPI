<?php

namespace App\Http\Requests\Slider;

use Illuminate\Validation\Rule;
use App\Repositories\Slider\SliderRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreSliderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => ['required', 'string', 'max:150'],
            'subtitle'    => ['nullable', 'string', 'max:200'],
            'image_label' => ['nullable', 'string', 'max:150'],
            'image'       => ['nullable', 'image', 'max:4096'],
            'image_url'   => ['nullable', 'string', 'max:500'],
            'badge'       => ['nullable', 'string', 'max:120'],
            'cta_text'    => ['nullable', 'string', 'max:60'],
            'cta_link'    => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['required', 'integer', 'min:0'],
            'status'      => ['required', Rule::in(SliderRepository::STATUSES)],
        ];
    }
}
