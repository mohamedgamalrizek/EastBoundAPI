<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\PasswordUpdateRequest;
use App\Http\Requests\Profile\UpdateRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $repo;

    public function __construct(UserInterface $repo)
    {
        $this->repo = $repo;
    }

    public function profile()
    {
        $user = auth()->user();
        return view('backend.profile.profile', compact('user'));
    }

    public function profileEdit()
    {
        $user = auth()->user();
        return view('backend.profile.profile-update', compact('user'));
    }

    public function passwordEdit()
    {
        $user = auth()->user();
        return view('backend.profile.password-update', compact('user'));
    }

    public function profileUpdate(UpdateRequest $request)
    {
        $result = $this->repo->profileUpdate($request);

        if ($result['status']) {
            return redirect()->route('profile')->with('success', $result['message']);
        }
        return  redirect()->route('profile.edit')->with('danger', $result['message'])->withInput();
    }

    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        $result = $this->repo->passwordUpdate($request);
        if ($result['status']) {
            return redirect()->route('profile')->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('danger', $result['message']);
    }
}
