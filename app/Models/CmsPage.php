<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_description',
        'body',
        'breadcrumb_label',
        'hero_subtitle',
        'story_image',
        'story_eyebrow',
        'story_heading',
        'story_lead',
        'story_body',
        'story_badges',
        'promo_badge',
        'promo_title',
        'promo_text',
        'promo_link',
        'stat_travelers',
        'stat_destinations',
        'stat_visa_success',
        'stat_experience',
        'search_placeholder',
        'all_label',
        'read_more_label',
        'empty_text',
        'filtered_empty_text',
        'contact_address_label',
        'contact_phone_label',
        'contact_email_label',
        'section_icon',
        'section_eyebrow',
        'section_heading',
        'form_title',
        'form_intro',
        'form_name_label',
        'form_name_placeholder',
        'form_phone_label',
        'form_phone_placeholder',
        'form_email_label',
        'form_email_placeholder',
        'form_notes_label',
        'form_notes_placeholder',
        'form_message_label',
        'form_message_placeholder',
        'subject_options',
        'form_submit_button',
        'success_message',
        'status',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
