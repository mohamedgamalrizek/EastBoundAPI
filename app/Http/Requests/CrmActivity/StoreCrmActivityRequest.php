<?php

namespace App\Http\Requests\CrmActivity;

use Illuminate\Validation\Rule;
use App\Repositories\CrmActivity\CrmActivityRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreCrmActivityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id'   => ['nullable', 'required_without:lead_id', 'exists:customers,id'],
            'lead_id'       => ['nullable', 'required_without:customer_id', 'exists:leads,id'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'type'          => ['required', Rule::in(CrmActivityRepository::TYPES)],
            'subject'       => ['required', 'string', 'max:150'],
            'body'          => ['nullable', 'string', 'max:1000'],
            'activity_date' => ['nullable', 'date'],
            'channel'       => ['nullable', Rule::in(CrmActivityRepository::CHANNELS)],
        ];
    }
}
