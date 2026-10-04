<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\LeaveRequest\LeaveRequestInterface;
use App\Http\Requests\LeaveRequest\StoreLeaveRequestRequest;
use App\Http\Requests\LeaveRequest\UpdateLeaveRequestRequest;

class LeaveRequestController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hr.leave';
    protected string $redirectRoute = 'hr.leave.index';

    protected ?array $listAnalyticsConfig = ['model' => 'LeaveRequest', 'group' => 'status', 'label' => 'Leave Requests'];

    public function __construct(LeaveRequestInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateLeaveRequestRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
