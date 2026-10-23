<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dmc_website_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('dmc_websites', 'id')->where('active', true)],
            'campaign_id' => ['nullable', 'required_if:campaign_source,Google Ads', 'integer', 'exists:campaigns,id'],
            'lost_reason' => ['nullable', 'required_if:stage,Lost', 'string', 'max:1000'],
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'email'    => ['nullable', 'email', 'max:255'],
            'interest' => ['nullable', 'string', 'max:255'],
            'source'   => ['required', 'in:Facebook,Website,Referral,WhatsApp,Instagram,Walk-in'],
            'website'  => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'in:English,Spanish,Italian,French'],
            'country'  => ['required', 'string', 'max:255'],
            'campaign_source' => ['required', 'in:Google Ads,Social Media,Organic Leads,Referral,Other'],
            'value'    => ['nullable', 'numeric', 'min:0'],
            'subject' => ['nullable', 'string', 'max:255'],
            'destinations' => ['nullable', 'string', 'max:255'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'adults' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'children' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'budget' => ['nullable', 'string', 'max:100'],
            'comments' => ['nullable', 'string', 'max:5000'],
            'created_at' => ['nullable', 'date'],
            'stage'    => ['required', 'in:New,Contacted,Proposal,Negotiation,Won,Lost'],
            'owner'    => ['nullable', 'string', 'max:255'],
            'notes'    => ['nullable', 'string'],
        ];
    }
}
