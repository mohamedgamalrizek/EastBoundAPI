<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Validation\Rule;
use App\Repositories\Campaign\CampaignRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'channel' => ['required', Rule::in(CampaignRepository::CHANNEL)],
            'audience' => ['nullable', 'string', 'max:191'],
            'budget' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(CampaignRepository::STATUS)],
        ];
    }
}
