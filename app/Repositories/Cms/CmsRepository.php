<?php

namespace App\Repositories\Cms;

use App\Models\CmsPage;
use App\Repositories\BaseRepository;
use App\Repositories\Cms\CmsInterface;

class CmsRepository extends BaseRepository implements CmsInterface
{
    /** Allowed option sets — mirror the cms_pages migration. */
    public const STATUSES = ['published', 'draft'];

    public function __construct(CmsPage $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'title'            => $request->title,
            'slug'             => $request->slug,
            'meta_description' => $request->meta_description,
            'body'             => $request->body,
            'breadcrumb_label'  => $request->breadcrumb_label,
            'hero_subtitle'     => $request->hero_subtitle,
            'story_image'       => $request->story_image,
            'story_eyebrow'     => $request->story_eyebrow,
            'story_heading'     => $request->story_heading,
            'story_lead'        => $request->story_lead,
            'story_body'        => $request->story_body,
            'story_badges'      => $request->story_badges,
            'promo_badge'       => $request->promo_badge,
            'promo_title'       => $request->promo_title,
            'promo_text'        => $request->promo_text,
            'promo_link'        => $request->promo_link,
            'stat_travelers'    => $request->stat_travelers,
            'stat_destinations' => $request->stat_destinations,
            'stat_visa_success' => $request->stat_visa_success,
            'stat_experience'   => $request->stat_experience,
            'search_placeholder'=> $request->search_placeholder,
            'all_label'         => $request->all_label,
            'read_more_label'   => $request->read_more_label,
            'empty_text'        => $request->empty_text,
            'filtered_empty_text' => $request->filtered_empty_text,
            'contact_address_label' => $request->contact_address_label,
            'contact_phone_label' => $request->contact_phone_label,
            'contact_email_label' => $request->contact_email_label,
            'section_icon'      => $request->section_icon,
            'section_eyebrow'   => $request->section_eyebrow,
            'section_heading'   => $request->section_heading,
            'form_title'        => $request->form_title,
            'form_intro'        => $request->form_intro,
            'form_name_label'   => $request->form_name_label,
            'form_name_placeholder' => $request->form_name_placeholder,
            'form_phone_label'  => $request->form_phone_label,
            'form_phone_placeholder' => $request->form_phone_placeholder,
            'form_email_label'  => $request->form_email_label,
            'form_email_placeholder' => $request->form_email_placeholder,
            'form_notes_label'  => $request->form_notes_label,
            'form_notes_placeholder' => $request->form_notes_placeholder,
            'form_message_label' => $request->form_message_label,
            'form_message_placeholder' => $request->form_message_placeholder,
            'subject_options'   => $request->subject_options,
            'form_submit_button' => $request->form_submit_button,
            'success_message'   => $request->success_message,
            'status'           => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
        ];
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function seo()
    {
        return [];
    }
}
