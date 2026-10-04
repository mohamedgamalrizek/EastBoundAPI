<?php

namespace App\Http\Requests\Review;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Moderation only. The rating and the text belong to the customer who wrote
 * them and are not editable from the back office — an agency that could edit
 * a review would make every review on the site worthless.
 */
class UpdateReviewRequest extends FormRequest
{
    public function authorize()
    {
        return hasPermission('review_update');
    }

    public function rules()
    {
        return [
            'id'     => ['required', 'integer', 'exists:reviews,id'],
            'status' => ['required', Rule::in(Review::STATUSES)],
            'reply'  => ['nullable', 'string', 'max:2000'],
        ];
    }
}
