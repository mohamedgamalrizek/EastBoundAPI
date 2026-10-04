<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Validation\Rule;
use App\Repositories\Announcement\AnnouncementRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'           => ['required', 'exists:announcements,id'],
            'title'        => ['required', 'string', 'max:150'],
            'body'         => ['nullable', 'string', 'max:1000'],
            'audience'     => ['required', Rule::in(AnnouncementRepository::AUDIENCES)],
            'published_on' => ['nullable', 'date'],
            'status'       => ['required', Rule::in(AnnouncementRepository::STATUSES)],
        ];
    }
}
