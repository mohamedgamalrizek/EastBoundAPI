<?php

namespace App\Http\Requests\Portal;

use Illuminate\Validation\Rule;
use App\Repositories\CustomerPortal\CustomerPortalRepository;
use Illuminate\Foundation\Http\FormRequest;

class PortalTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject'    => ['required', 'string', 'max:150'],
            'priority'   => ['required', Rule::in(CustomerPortalRepository::TICKET_PRIORITIES)],
            'department' => ['nullable', Rule::in(CustomerPortalRepository::TICKET_DEPARTMENTS)],
        ];
    }
}
