<?php

namespace App\Http\Requests\Support;

use Illuminate\Validation\Rule;
use App\Repositories\Support\SupportRepository;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A ticket raised by the account itself — an agent from the portal or the
 * mobile app — rather than keyed in by the desk.
 *
 * Only the two fields the raiser can answer for. The reference, the name and
 * the opening status are set server-side, and routing (department, assignee)
 * stays with the desk in the admin module.
 */
class RaiseTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject'  => ['required', 'string', 'max:150'],
            'priority' => ['nullable', Rule::in(SupportRepository::PRIORITIES)],
        ];
    }
}
