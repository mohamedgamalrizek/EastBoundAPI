<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SignupRequest;
use App\Http\Requests\User\TokenVerificationRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    private  $userRepo;

    public function __construct(UserInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function registerForm()
    {
        return view('auth.register.register_form');
    }

    public function register(SignupRequest $request)
    {
        $result = $this->userRepo->signup($request);
        if ($result['status']) {
            return redirect()->route('register.tokenForm')->with('success', $result['message']);;
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function tokenForm()
    {
        $user = $this->userRepo->get(session('user_id'));
        if ($user->email_verified_at == null) {
            return view('auth.otp_form', ['purpose' => 'register']);
        }
        return redirect('/');
    }

    public function verifyToken(TokenVerificationRequest $request)
    {
        $result =  $this->userRepo->verifyToken($request);

        if ($result['status']) {
            if (auth()->attempt(['email' => session()->pull('email'), 'password' => session()->pull('password')])) {
                return redirect(auth()->user()->home());
            }
            return redirect()->route('login');
        }

        return redirect()->back()->with('danger', $result['message']);
    }
}
