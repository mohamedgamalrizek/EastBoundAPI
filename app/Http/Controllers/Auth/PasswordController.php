<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\EmailVerifyRequest;
use App\Http\Requests\User\PasswordResetRequest;
use App\Http\Requests\User\TokenVerificationRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    private  $userRepo;

    public function __construct(UserInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function passwordForgotForm()
    {
        return view('auth.password.forgot');
    }

    public function verifyEmail(EmailVerifyRequest $request)
    {
        $result =  $this->userRepo->passwordResetToken($request);

        if ($result['status']) {
            return redirect()->route('password.tokenForm')->with('success', $result['message']);
        }

        return redirect()->back()->with('danger', $result['message']);
    }

    public function tokenForm()
    {
        if (session()->has('email')) {
            return view('auth.otp_form', ['purpose' => 'reset']);
        }
        return redirect('/');
    }


    public function verifyToken(TokenVerificationRequest $request)
    {
        $result =  $this->userRepo->verifyToken($request);

        if ($result['status']) {
            session(['password_reset' => true, 'token' => $request->token]);
            return redirect()->route('password.resetForm')->with('success', $result['message']);
        }
        return redirect()->back()->with('danger', $result['message']);
    }

    public function passwordResetForm()
    {
        if (session()->has('password_reset') && session('password_reset') == true) {
            return view('auth.password.reset');
        }
        return redirect()->back();
    }

    public function passwordReset(PasswordResetRequest $request)
    {
        $result =  $this->userRepo->passwordReset($request);

        if ($result['status']) {

            session()->forget(['password_reset', 'token']);

            if (auth()->attempt(['email' => session()->pull('email'), 'password' => $request->new_password])) {
                return redirect('/');
            }
            return redirect()->route('login');
        }

        return redirect()->back()->with('danger', $result['message']);
    }
}
