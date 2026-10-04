<?php

namespace App\Http\Requests\Faq;

use Illuminate\Validation\Rule;
use App\Repositories\Faq\FaqRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'       => ['required', 'exists:faqs,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer'   => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'status'   => ['required', Rule::in(FaqRepository::STATUSES)],
        ];
    }
}
