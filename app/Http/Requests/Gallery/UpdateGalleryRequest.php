<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Validation\Rule;
use App\Repositories\Gallery\GalleryRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'          => ['required', 'exists:galleries,id'],
            'title'       => ['required', 'string', 'max:150'],
            'image_label' => ['nullable', 'string', 'max:150'],
            'image'       => ['nullable', 'image', 'max:4096'],
            'image_url'   => ['nullable', 'string', 'max:500'],
            'category'    => ['nullable', 'string', 'max:100'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'status'      => ['required', Rule::in(GalleryRepository::STATUSES)],
        ];
    }
}
