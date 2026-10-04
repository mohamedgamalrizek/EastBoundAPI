<?php

namespace App\Http\Requests\Portal;

use App\Repositories\Transport\TransportRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortalBookTransportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type'        => ['required', Rule::in(TransportRepository::TYPES)],
            'route'       => ['required', 'string', 'max:190'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'vehicle'     => ['nullable', 'string', 'max:120'],
        ];
    }
}
