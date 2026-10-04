<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'email'    => ['nullable', 'email', 'max:255'],
            'interest' => ['nullable', 'string', 'max:255'],
            'source'   => ['required', 'in:Facebook,Website,Referral,WhatsApp,Instagram,Walk-in'],
            'value'    => ['nullable', 'numeric', 'min:0'],
            'stage'    => ['required', 'in:New,Contacted,Proposal,Negotiation,Won,Lost'],
            'owner'    => ['nullable', 'string', 'max:255'],
            'notes'    => ['nullable', 'string'],
        ];
    }
}
