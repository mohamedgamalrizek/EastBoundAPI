<?php

namespace App\Repositories\StaffAttendance;

use App\Models\User;
use App\Models\StaffAttendance;
use App\Repositories\BaseRepository;
use App\Repositories\StaffAttendance\StaffAttendanceInterface;

class StaffAttendanceRepository extends BaseRepository implements StaffAttendanceInterface
{
    /** Allowed option sets — mirror the staff_attendance migration. */
    public const STATUSES = ['Present', 'Leave', 'Absent'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['user'];

    public function __construct(StaffAttendance $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'user_id'    => $request->user_id,
            'staff_name' => $request->staff_name,
            'date'       => $request->date,
            'check_in'   => $request->check_in,
            'check_out'  => $request->check_out,
            'hours'      => $request->hours,
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
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'users'    => User::orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ];
    }
}
