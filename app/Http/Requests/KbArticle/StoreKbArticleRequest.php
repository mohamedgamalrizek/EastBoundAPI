<?php

namespace App\Http\Requests\KbArticle;

use Illuminate\Validation\Rule;
use App\Repositories\KbArticle\KbArticleRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreKbArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'    => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'excerpt'  => ['nullable', 'string', 'max:500'],
            'status'   => ['required', Rule::in(KbArticleRepository::STATUSES)],
        ];
    }
}
