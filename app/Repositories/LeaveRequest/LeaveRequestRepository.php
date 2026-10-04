<?php

namespace App\Repositories\LeaveRequest;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Repositories\BaseRepository;
use App\Repositories\LeaveRequest\LeaveRequestInterface;

class LeaveRequestRepository extends BaseRepository implements LeaveRequestInterface
{
    /** Allowed option sets — mirror the leave_requests migration. */
    public const LEAVE_TYPES = ['Casual', 'Sick', 'Annual'];
    public const STATUSES    = ['Pending', 'Approved', 'Rejected'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['user'];

    public function __construct(LeaveRequest $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'user_id'    => $request->user_id,
            'staff_name' => $request->staff_name,
            'leave_type' => $request->leave_type,
            'from_date'  => $request->from_date,
            'to_date'    => $request->to_date,
            'days'       => $request->days,
            'reason'     => $request->reason,
            'status'     => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('staff_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'leaveTypes' => self::LEAVE_TYPES,
            'statuses'   => self::STATUSES,
        ];
    }
}
