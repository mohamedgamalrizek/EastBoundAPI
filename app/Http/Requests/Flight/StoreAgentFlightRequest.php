<?php

namespace App\Http\Requests\Flight;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An agent selling a flight to their client, from the portal or the app.
 *
 * No pnr, ticket number or fare field: the request opens Pending with a
 * placeholder PNR at fare 0, and the desk prices it and issues the ticket —
 * the commission is keyed on ticketing, so there is nothing for the agent to
 * quote or inflate here.
 *
 * The origin and the destination are asked for separately (From / To) so an
 * agent cannot file a half-written route, and the two are joined back into the
 * single `route` the booking stores — everything downstream (the bookings
 * list, invoices, reports) keeps reading one string.
 */
class StoreAgentFlightRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Compose the stored route from the two boxes the form actually shows.
     * Only when both are filled: a lone From must fail on the To field, not
     * on a `route` field the agent cannot see.
     */
    protected function prepareForValidation()
    {
        $from = trim((string) $this->input('from'));
        $to   = trim((string) $this->input('to'));

        if ($from !== '' && $to !== '') {
            $this->merge(['route' => $from . '-' . $to]);
        }
    }

    public function rules()
    {
        return [
            // `route` is still accepted on its own so app builds released
            // before the From/To split keep working; the portal and the
            // current app post from/to and the route is composed above.
            'from'           => ['required_without:route', 'nullable', 'string', 'max:60'],
            'to'             => ['required_without:route', 'nullable', 'string', 'max:60', 'different:from'],
            'route'          => ['required_without_all:from,to', 'string', 'max:120'],
            'flight_date'    => ['required', 'date', 'after_or_equal:today'],
            'passenger_name' => ['required', 'string', 'max:255'],
            'airline'        => ['nullable', 'string', 'max:120'],
            'client_name'    => ['required', 'string', 'max:255'],
            'client_phone'   => ['nullable', 'string', 'max:30'],
            'client_email'   => ['nullable', 'email', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'from' => 'From',
            'to'   => 'To',
        ];
    }

    public function messages()
    {
        return [
            'from.required_without' => ___('alert.enter_flight_from'),
            'to.required_without'   => ___('alert.enter_flight_to'),
            'to.different'          => ___('alert.from_to_same_airport'),
            'route.required_without_all' => ___('alert.enter_flight_from_and_to'),
        ];
    }
}
