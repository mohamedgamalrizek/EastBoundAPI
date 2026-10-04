<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Validation\Rule;
use App\Repositories\Testimonial\TestimonialRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'      => ['required', 'exists:testimonials,id'],
            'name'    => ['required', 'string', 'max:100'],
            'role'    => ['nullable', 'string', 'max:100'],
            'avatar'     => ['nullable', 'image', 'max:4096'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'city'    => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:500'],
            'rating'  => ['required', 'integer', 'between:1,5'],
            'status'  => ['required', Rule::in(TestimonialRepository::STATUSES)],
        ];
    }
}
