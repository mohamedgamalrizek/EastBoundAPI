<?php

namespace App\Http\Requests\TransportService;

use Illuminate\Validation\Rule;
use App\Models\TransportService;
use App\Repositories\TransportService\TransportServiceRepository;
use App\Http\Controllers\FrontendController;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransportServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id'           => ['required', 'exists:transport_services,id'],
            'title'        => ['required', 'string', 'max:255'],
            'icon'         => ['nullable', Rule::in(array_keys(TransportServiceRepository::ICONS))],
            'description'  => ['nullable', 'string', 'max:1000'],
            'price_from'   => ['nullable', 'numeric', 'min:0'],
            'price_unit'   => ['nullable', 'string', 'max:20'],
            'booking_type' => ['required', Rule::in(array_keys(FrontendController::bookingTypes()))],
            'vehicle_type' => ['nullable', Rule::in(TransportService::VEHICLE_TYPES)],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'status'       => ['required', Rule::in(TransportServiceRepository::STATUSES)],
        ];
    }
}
