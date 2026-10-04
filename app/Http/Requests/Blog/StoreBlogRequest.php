<?php

namespace App\Http\Requests\Blog;

use Illuminate\Validation\Rule;
use App\Repositories\Blog\BlogRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'        => ['required', 'string', 'max:150'],
            'slug'         => ['required', 'string', 'max:170', 'unique:blogs,slug'],
            'author'       => ['nullable', 'string', 'max:100'],
            'category'     => ['nullable', 'string', 'max:100'],
            'image'        => ['nullable', 'image', 'max:4096'],
            'image_url'    => ['nullable', 'string', 'max:500'],
            'excerpt'      => ['nullable', 'string', 'max:500'],
            'body'         => ['nullable', 'string'],
            'read_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'status'       => ['required', Rule::in(BlogRepository::STATUSES)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
