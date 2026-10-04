<?php

namespace App\Repositories\Passport;

use App\Models\Customer;
use App\Models\Passport;
use App\Repositories\BaseRepository;
use App\Repositories\Passport\PassportInterface;

class PassportRepository extends BaseRepository implements PassportInterface
{
    /** Allowed option sets — mirror the passports migration. */
    public const STATUSES = [
        Passport::STATUS_VALID,
        Passport::STATUS_EXPIRING,
        Passport::STATUS_EXPIRED,
    ];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer'];

    public function __construct(Passport $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'  => $request->customer_id,
            'holder_name'  => $request->holder_name,
            'passport_no'  => $request->passport_no,
            'nationality'  => $request->nationality,
            'issue_date'   => $request->issue_date,
            'expiry_date'  => $request->expiry_date,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('holder_name', 'like', "%{$search}%")
                  ->orWhere('passport_no', 'like', "%{$search}%");
            });
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'statuses'  => self::STATUSES,
        ];
    }
}
