<?php

namespace App\Http\Requests\Cms;

use Illuminate\Validation\Rule;
use App\Repositories\Cms\CmsRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'     => ['required', 'exists:cms_pages,id'],
            'title'  => ['required', 'string', 'max:150'],
            'slug'   => ['required', 'string', 'max:170', Rule::unique('cms_pages', 'slug')->ignore($this->input('id'))],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'body'   => ['nullable', 'string'],
            'breadcrumb_label' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'story_image' => ['nullable', 'string', 'max:500'],
            'story_eyebrow' => ['nullable', 'string', 'max:255'],
            'story_heading' => ['nullable', 'string', 'max:255'],
            'story_lead' => ['nullable', 'string', 'max:500'],
            'story_body' => ['nullable', 'string'],
            'story_badges' => ['nullable', 'string', 'max:500'],
            'promo_badge' => ['nullable', 'string', 'max:255'],
            'promo_title' => ['nullable', 'string', 'max:255'],
            'promo_text' => ['nullable', 'string', 'max:500'],
            'promo_link' => ['nullable', 'string', 'max:255'],
            'stat_travelers' => ['nullable', 'string', 'max:255'],
            'stat_destinations' => ['nullable', 'string', 'max:255'],
            'stat_visa_success' => ['nullable', 'string', 'max:255'],
            'stat_experience' => ['nullable', 'string', 'max:255'],
            'search_placeholder' => ['nullable', 'string', 'max:255'],
            'all_label' => ['nullable', 'string', 'max:255'],
            'read_more_label' => ['nullable', 'string', 'max:255'],
            'empty_text' => ['nullable', 'string', 'max:500'],
            'filtered_empty_text' => ['nullable', 'string', 'max:500'],
            'contact_address_label' => ['nullable', 'string', 'max:255'],
            'contact_phone_label' => ['nullable', 'string', 'max:255'],
            'contact_email_label' => ['nullable', 'string', 'max:255'],
            'section_icon' => ['nullable', 'string', 'max:100'],
            'section_eyebrow' => ['nullable', 'string', 'max:255'],
            'section_heading' => ['nullable', 'string', 'max:255'],
            'form_title' => ['nullable', 'string', 'max:255'],
            'form_intro' => ['nullable', 'string', 'max:500'],
            'form_name_label' => ['nullable', 'string', 'max:255'],
            'form_name_placeholder' => ['nullable', 'string', 'max:255'],
            'form_phone_label' => ['nullable', 'string', 'max:255'],
            'form_phone_placeholder' => ['nullable', 'string', 'max:255'],
            'form_email_label' => ['nullable', 'string', 'max:255'],
            'form_email_placeholder' => ['nullable', 'string', 'max:255'],
            'form_notes_label' => ['nullable', 'string', 'max:255'],
            'form_notes_placeholder' => ['nullable', 'string', 'max:500'],
            'form_message_label' => ['nullable', 'string', 'max:255'],
            'form_message_placeholder' => ['nullable', 'string', 'max:500'],
            'subject_options' => ['nullable', 'string'],
            'form_submit_button' => ['nullable', 'string', 'max:255'],
            'success_message' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(CmsRepository::STATUSES)],
        ];
    }
}
