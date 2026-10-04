<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Enums\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\User\UserInterface;
use App\Repositories\LoginActivity\LoginActivityInterface;

class AuthController extends Controller
{

    private  $userRepo, $LoginActivity;


    public function __construct(UserInterface $userRepo, LoginActivityInterface $LoginActivity)
    {
        $this->userRepo = $userRepo;
        $this->LoginActivity = $LoginActivity;
    }


    // ---------------------------------------------------------------------
    // Portal login (public) — for Customers & Agents. Linked from the website.
    // Admin users may sign in here too; everyone is redirected to their own home.
    // ---------------------------------------------------------------------
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        return $this->attemptLogin($request);
    }

    // ---------------------------------------------------------------------
    // Admin login (staff entrance) — only admin-type users are allowed in.
    // Lives at a separate URL so it isn't exposed to customers/agents.
    // ---------------------------------------------------------------------
    public function adminLoginForm()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        return $this->attemptLogin($request, adminOnly: true);
    }

    /**
     * Shared credential check + session start used by both login entrances.
     *
     * @param  bool  $adminOnly  when true, portal users are rejected.
     */
    private function attemptLogin(Request $request, bool $adminOnly = false)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
        ]);

        $user = User::query()->firstWhere('email', $request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.'
            ])->withInput($request->only('email'));
        }

        // Block accounts that are not active (unverified email or deactivated by admin).
        if ($user->status !== Status::ACTIVE) {
            return back()->withErrors([
                'email' => 'Your account is not active. Please verify your email or contact support.'
            ])->withInput($request->only('email'));
        }

        // In SaaS mode the central domain IS the platform: only the SaaS super
        // admin (saas_read) may sign in here. Company users belong to a tenant
        // workspace, not the central app — they sign in at their own company
        // domain once it is created, never centrally.
        if (config('saas.enabled') && !$user->can_access('saas_read')) {
            return back()->withErrors([
                'email' => 'This is the FLOW platform sign-in. Your company has its own workspace — please sign in there.'
            ])->withInput($request->only('email'));
        }

        // Keep the admin entrance closed to portal (customer/agent) users.
        // Company staff (isAdminUser) always; the SaaS platform owner only when
        // SaaS mode is on (in single mode saas_read grants no panel).
        $saasOwner = config('saas.enabled') && $user->can_access('saas_read');
        if ($adminOnly && !$user->isAdminUser() && !$saasOwner) {
            return back()->withErrors([
                'email' => 'These credentials are not authorized for the admin panel.'
            ])->withInput($request->only('email'));
        }

        if (auth()->attempt($request->only(['email', 'password']))) {
            // Active Remember me 24 houre
            if ($request->remember != null) {
                Cookie::queue('email', $request->email, 1440);
                Cookie::queue('password', $request->password, 1440);
            } else {
                Cookie::queue(Cookie::forget('email'));
                Cookie::queue(Cookie::forget('password'));
            }

            //add user  login activity
            if (Auth::check()) :
                $this->LoginActivity->addLoginActivity(request()->header('user_agent'), 'user_logged_in');
            endif;
            //add user  login activity

            return redirect($user->home());
        }

        return back()->withErrors([
            'email' => 'Unable to sign you in. Please try again.'
        ])->withInput($request->only('email'));
    }

    public function logout()
    {
        //add user  logout activity
        if (Auth::check()) :
            $this->LoginActivity->addLoginActivity(request()->header('user_agent'), 'user_logged_out');
        endif;
        //add user  logout activity

        session()->flush();

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return request()->wantsJson() ? response()->json(status: 204) : redirect('/');
    }

    public function resendToken(Request $request)
    {
        $result =  $this->userRepo->resendToken($request);

        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']], 200);
            }
            return redirect()->back()->with('success', $result['message']);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return redirect()->back()->with('danger', $result['message']);
    }
}
