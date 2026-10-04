<?php

namespace App\Repositories\StaffPortal;

use App\Models\Task;
use App\Models\StaffAttendance;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Traits\ReturnFormatTrait;
use App\Repositories\StaffPortal\StaffPortalInterface;

class StaffPortalRepository implements StaffPortalInterface
{
    use ReturnFormatTrait;

    /** Leave options for the staff's own requests. */
    public const LEAVE_TYPES = ['Casual', 'Sick', 'Annual'];

    /** The logged-in staff member's user id — every page is scoped to it. */
    protected function staffId(): int
    {
        return (int) auth()->id();
    }

    public function dashboard()
    {
        $uid = $this->staffId();

        return [
            'myTasks'     => Task::where('assigned_to', $uid)->count(),
            'dueToday'    => Task::where('assigned_to', $uid)->whereDate('due_date', today())->count(),
            'completed'   => Task::where('assigned_to', $uid)->where('status', 'Done')->count(),
            'present'     => StaffAttendance::where('user_id', $uid)->where('status', 'Present')->count(),
            'recentTasks' => Task::where('assigned_to', $uid)->latest()->take(8)->get(),
        ];
    }

    public function tasks()
    {
        return ['tasks' => Task::where('assigned_to', $this->staffId())->with('assignedTo')->latest()->get()];
    }

    public function attendance()
    {
        return ['attendance' => StaffAttendance::where('user_id', $this->staffId())->latest('date')->get()];
    }

    public function payslips()
    {
        return ['payslips' => Payslip::where('user_id', $this->staffId())->latest('month')->get()];
    }

    public function profile()
    {
        return ['user' => auth()->user()];
    }

    /** Update the logged-in staff member's own profile (never email/role/password here). */
    public function updateProfile($request)
    {
        try {
            $user = auth()->user();
            $user->name          = $request->name;
            $user->phone         = $request->phone;
            $user->address       = $request->address;
            $user->date_of_birth = $request->date_of_birth ?: null;
            $user->about         = $request->about;
            $user->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /* ---------------------------------------------------------------------
     | Leave requests — staff self-service (scoped to the current staff user)
     * ------------------------------------------------------------------- */

    public function leave()
    {
        return [
            'leaves'     => LeaveRequest::where('user_id', $this->staffId())->latest('from_date')->get(),
            'leaveTypes' => self::LEAVE_TYPES,
        ];
    }

    /** Submit a new leave request for the logged-in staff member (status Pending). */
    public function storeLeave($request)
    {
        try {
            $user = auth()->user();
            $days = $this->dayCount($request->from_date, $request->to_date);

            LeaveRequest::create([
                'user_id'    => $user->id,
                'staff_name' => $user->name,
                'leave_type' => $request->leave_type,
                'from_date'  => $request->from_date,
                'to_date'    => $request->to_date,
                'days'       => $days,
                'reason'     => $request->reason,
                'status'     => 'Pending',
            ]);

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /** Inclusive day count between two dates. */
    private function dayCount($from, $to): int
    {
        return \Illuminate\Support\Carbon::parse($from)->diffInDays(\Illuminate\Support\Carbon::parse($to)) + 1;
    }
}
