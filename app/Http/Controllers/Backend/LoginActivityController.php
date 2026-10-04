<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\LoginActivity\LoginActivityInterface;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class LoginActivityController extends Controller
{
    private $repo;

    public function __construct(LoginActivityInterface $repo)
    {
        $this->repo   = $repo;
    }
    public function index()
    {

        $loginActivities = $this->repo->getLatest();
        return view('backend.login_activity.index', compact('loginActivities'));
    }
}
