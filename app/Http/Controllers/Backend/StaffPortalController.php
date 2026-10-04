<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\StaffPortal\StaffPortalInterface;
use App\Http\Requests\Portal\PortalProfileRequest;
use App\Http\Requests\Portal\PortalLeaveRequest;

class StaffPortalController extends Controller
{
    protected $repo;

    public function __construct(StaffPortalInterface $repo)
    {
        $this->repo = $repo;
    }

    public function dashboard()
    {
        return view('backend.portal.staff.dashboard', $this->repo->dashboard());
    }

    public function tasks()
    {
        return view('backend.portal.staff.tasks', $this->repo->tasks());
    }

    public function attendance()
    {
        return view('backend.portal.staff.attendance', $this->repo->attendance());
    }

    public function leave()
    {
        return view('backend.portal.staff.leave', $this->repo->leave());
    }

    public function leaveCreate()
    {
        return view('backend.portal.staff.leave-create', $this->repo->leave());
    }

    public function leaveStore(PortalLeaveRequest $request)
    {
        return $this->staffRedirect($this->repo->storeLeave($request), 'staff.leave');
    }

    public function payslips()
    {
        return view('backend.portal.staff.payslips', $this->repo->payslips());
    }

    public function profile()
    {
        return view('backend.portal.staff.profile', $this->repo->profile());
    }

    public function profileEdit()
    {
        return view('backend.portal.staff.profile-edit', $this->repo->profile());
    }

    public function profileUpdate(PortalProfileRequest $request)
    {
        return $this->staffRedirect($this->repo->updateProfile($request), 'staff.profile');
    }

    /** Apply a self-service result to a redirect back to its staff page. */
    private function staffRedirect(array $result, string $route)
    {
        if ($result['status']) {
            return redirect()->route($route)->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }
}
