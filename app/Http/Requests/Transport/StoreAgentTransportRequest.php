<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use App\Repositories\Transport\TransportRepository;
use Illuminate\Foundation\Http\FormRequest;

/**
 * An agent selling a transport trip to their client, from the portal or the
 * app.
 *
 * No fare field: the trip opens Pending with fare 0 and the desk prices it on
 * confirmation — the commission is keyed on payment, so there is nothing for
 * the agent to quote or inflate here.
 */
class StoreAgentTransportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type'         => ['required', Rule::in(TransportRepository::TYPES)],
            'direction'    => ['nullable', Rule::in(TransportRepository::DIRECTIONS)],
            'client_name'  => ['required', 'string', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'route'        => ['required', 'string', 'max:190'],
            'travel_date'  => ['required', 'date', 'after_or_equal:today'],
            'vehicle'      => ['nullable', 'string', 'max:120'],
            'driver_id'    => ['nullable', 'integer', 'exists:drivers,id'],
            // The agent quotes the client a fare when they know one (a
            // budgeted customer, an operator's rate); blank means the desk
            // prices it on confirmation.
            'fare'         => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ];
    }
}
