<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Account preferences for the mobile app — the server-side home the Settings
 * screen never had. Same JSON column and keys the web portal's Preferences
 * page writes (users.preferences / customers.preferences), so a choice made
 * on one surface shows up on the other.
 */
class PreferencesController extends Controller
{
    use ApiReturnFormatTrait;

    /** Keys the app may store, with their defaults. */
    private const DEFAULTS = [
        'language'            => 'en',   // en | bn
        'currency'            => 'BDT',  // BDT | USD
        'email_notifications' => true,
        'sms_alerts'          => true,
    ];

    public function show(Request $request)
    {
        return $this->responseWithSuccess('Preferences fetched.', [
            'preferences' => $this->merged($request->user()->preferences),
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'            => ['sometimes', Rule::in(['en', 'bn'])],
            'currency'            => ['sometimes', Rule::in(['BDT', 'USD'])],
            'email_notifications' => ['sometimes', 'boolean'],
            'sms_alerts'          => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $account = $request->user(); // Customer or User — both carry `preferences`

        // Partial update: only the keys the client sent change.
        $account->preferences = array_merge(
            $this->merged($account->preferences),
            $validator->validated()
        );
        $account->save();

        return $this->responseWithSuccess('Preferences saved.', [
            'preferences' => $this->merged($account->preferences),
        ]);
    }

    private function merged($stored): array
    {
        return array_merge(self::DEFAULTS, array_intersect_key(
            (array) $stored, self::DEFAULTS
        ));
    }
}
