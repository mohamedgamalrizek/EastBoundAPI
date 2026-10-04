<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\StaffAttendance\StaffAttendanceInterface;
use App\Http\Requests\StaffAttendance\StoreStaffAttendanceRequest;
use App\Http\Requests\StaffAttendance\UpdateStaffAttendanceRequest;

class StaffAttendanceController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hr.attendance';
    protected string $redirectRoute = 'hr.attendance.index';

    protected ?array $listAnalyticsConfig = ['model' => 'StaffAttendance', 'group' => 'status', 'label' => 'Attendance'];

    public function __construct(StaffAttendanceInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreStaffAttendanceRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateStaffAttendanceRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
