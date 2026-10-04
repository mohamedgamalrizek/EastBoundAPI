<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Whether this customer may review this booking at all is not a validation
 * rule — it depends on the booking's status and travel date — so it stays in
 * ReviewService and is checked in the repository. This only shapes the input.
 */
class PortalReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'title'      => ['nullable', 'string', 'max:120'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ];
    }
}
