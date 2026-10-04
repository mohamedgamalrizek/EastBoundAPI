<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use App\Repositories\Transport\TransportRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Compose the stored route from the From/To boxes the form shows.
     * Only when both are filled: a lone From must fail on the To field, not
     * on a `route` field the form no longer displays.
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
            'customer_id'   => ['nullable', 'exists:customers,id'],
            'agent_id'      => ['nullable', 'integer', 'exists:users,id'],
            'booking_id'    => ['nullable', 'exists:bookings,id'],
            'driver_id'     => ['nullable', 'exists:drivers,id'],
            'booking_no'    => ['required', 'string', 'max:50', 'unique:transport_bookings,booking_no'],
            'type'          => ['required', Rule::in(TransportRepository::TYPES)],
            'direction'     => ['nullable', Rule::in(TransportRepository::DIRECTIONS)],
            'customer_name' => ['required', 'string', 'max:100'],
            'from'          => ['required_without:route', 'nullable', 'string', 'max:60'],
            'to'            => ['required_without:route', 'nullable', 'string', 'max:60', 'different:from'],
            'route'         => ['required_without_all:from,to', 'string', 'max:100'],
            'travel_date'   => ['required', 'date'],
            'vehicle'       => ['nullable', 'string', 'max:100'],
            'fare'          => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(TransportRepository::STATUSES)],
            'payment_method' => ['nullable', Rule::in(\App\Services\Accounting\BillingService::PAYMENT_METHODS)],
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
            'from.required_without' => ___('alert.enter_trip_from'),
            'to.required_without'   => ___('alert.enter_trip_to'),
            'to.different'          => ___('alert.from_to_same_place'),
            'route.required_without_all' => ___('alert.enter_trip_from_and_to'),
        ];
    }
}
