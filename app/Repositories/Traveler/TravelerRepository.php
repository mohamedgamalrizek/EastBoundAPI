<?php

namespace App\Repositories\Traveler;

use App\Models\Customer;
use App\Models\Traveler;
use App\Repositories\BaseRepository;
use App\Repositories\Traveler\TravelerInterface;

class TravelerRepository extends BaseRepository implements TravelerInterface
{
    /** Allowed option sets — mirror the travelers migration. */
    public const RELATIONS = ['Self', 'Spouse', 'Child', 'Parent'];
    public const STATUSES  = ['Active', 'Inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer'];

    public function __construct(Traveler $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id' => $request->customer_id,
            'name'        => $request->name,
            'relation'    => $request->relation,
            'passport_no' => $request->passport_no,
            'nationality' => $request->nationality,
            'dob'         => $request->dob,
            'status'      => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('passport_no', 'like', "%{$search}%");
            });
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['relation'])) {
            $query->where('relation', $filters['relation']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'relations' => self::RELATIONS,
            'statuses'  => self::STATUSES,
        ];
    }
}
