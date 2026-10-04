<?php

namespace App\Repositories\Payslip;

use App\Models\User;
use App\Models\Payslip;
use App\Repositories\BaseRepository;
use App\Repositories\Payslip\PayslipInterface;

class PayslipRepository extends BaseRepository implements PayslipInterface
{
    /** Allowed option sets — mirror the payslips migration. */
    public const STATUSES = ['Paid', 'Pending'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['user'];

    public function __construct(Payslip $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'user_id'    => $request->user_id,
            'staff_name' => $request->staff_name,
            'month'      => $request->month,
            'basic'      => $request->basic,
            'allowances' => $request->allowances,
            'deductions' => $request->deductions,
            'net_pay'    => $request->net_pay,
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
