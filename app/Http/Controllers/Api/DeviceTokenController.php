<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * FCM registration tokens for the signed-in account (customer, agent, staff
 * or guide alike — pushes go to whoever owns the session on that device).
 */
class DeviceTokenController extends Controller
{
    use ApiReturnFormatTrait;

    /** Register (or re-own) this device's token. Idempotent. */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        DeviceToken::register($request->user(), $request->token, $request->platform);

        return $this->responseWithSuccess('Device registered for notifications.');
    }

    /** Forget this device's token — called on logout. */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'max:512'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        // Only the owner can unregister it; someone else's token is a no-op.
        DeviceToken::where('token', $request->token)
            ->where('notifiable_type', get_class($request->user()))
            ->where('notifiable_id', $request->user()->id)
            ->delete();

        return $this->responseWithSuccess('Device unregistered.');
    }
}
