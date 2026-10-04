<?php

namespace App\Http\Requests\Blog;

use Illuminate\Validation\Rule;
use App\Repositories\Blog\BlogRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'           => ['required', 'exists:blogs,id'],
            'title'        => ['required', 'string', 'max:150'],
            'slug'         => ['required', 'string', 'max:170', Rule::unique('blogs', 'slug')->ignore($this->input('id'))],
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
