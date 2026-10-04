<?php

namespace App\Repositories\LoginActivity;

use App\Models\Backend\LoginActivity;
use App\Repositories\LoginActivity\LoginActivityInterface;
use Illuminate\Support\Facades\Auth;

class LoginActivityRepository implements LoginActivityInterface
{

    public function getLatest()
    {
        return LoginActivity::latest()->paginate(10);
    }

    /**
     * Record a login/logout.
     *
     * Everything optional is resolved defensively: the country lookup needs a
     * package that may not be installed, and the whole method is wrapped in a
     * try/catch, so anything that throws here silently produced no log at all —
     * which is exactly why this table used to stay empty.
     */
    public function addLoginActivity($user_agent, $activity = '')
    {
        try {
            $ip       = request()->ip();
            $location = $this->location($ip);

            $loginActivity                = new LoginActivity();
            $loginActivity->user_id       = Auth::id();
            $loginActivity->activity      = $activity;
            $loginActivity->ip            = $ip;
            $loginActivity->browser       = UserBrowser($user_agent);
            $loginActivity->os            = UserOS($user_agent);
            $loginActivity->device        = UserDevice($user_agent);
            $loginActivity->country       = $location['country'];
            $loginActivity->country_code   = $location['country_code'];
            $loginActivity->save();

            return true;
        } catch (\Throwable $th) {
            report($th);

            return false;
        }
    }

    /**
     * Best-effort geo lookup. Returns empty strings when the optional
     * stevebauman/location package is absent or cannot place the address
     * (local and private IPs never resolve).
     */
    private function location(?string $ip): array
    {
        $empty = ['country' => '', 'country_code' => ''];

        if (! $ip || ! class_exists(\Stevebauman\Location\Facades\Location::class)) {
            return $empty;
        }

        try {
            $position = \Stevebauman\Location\Facades\Location::get($ip);
        } catch (\Throwable $th) {
            return $empty;
        }

        if (! $position) {
            return $empty;
        }

        return [
            'country'      => $position->countryName ?? '',
            'country_code' => strtolower($position->countryCode ?? ''),
        ];
    }
}
