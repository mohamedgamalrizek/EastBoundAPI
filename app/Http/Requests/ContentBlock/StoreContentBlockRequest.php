<?php

namespace App\Http\Requests\ContentBlock;

use App\Models\ContentBlock;
use App\Repositories\ContentBlock\ContentBlockRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentBlockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Only sections the frontend actually renders.
            'section'    => ['required', Rule::in(array_keys(ContentBlock::SECTIONS))],
            'icon'       => ['nullable', 'string', 'max:60'],
            'title'      => ['required', 'string', 'max:150'],
            'body'       => ['nullable', 'string', 'max:1000'],
            'url'        => ['nullable', 'string', 'max:200'],
            'image'      => ['nullable', 'image', 'max:4096'],
            'image_url'  => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status'     => ['required', Rule::in(ContentBlockRepository::STATUSES)],
        ];
    }
}
