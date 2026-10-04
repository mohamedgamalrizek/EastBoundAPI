<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string',
            'phone'         => 'required|regex:/^\+?[0-9]{1,4}-?[0-9]{7,14}$/',
            'email'         => 'required|email',
            'copyright'     => 'required|string',
            // svg intentionally excluded: both files are rendered inline (the
            // logo tag and the favicon link go straight into every page's
            // <head>/<body>), so an uploaded SVG's embedded <script>/onload
            // would execute in the browser of anyone who loads the site —
            // stored XSS. png/jpg/jpeg/webp/ico can't carry script.
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5098',
            'favicon'       => 'nullable|image|mimes:png,jpg,jpeg,webp,ico|max:5000',
        ];
    }
}
