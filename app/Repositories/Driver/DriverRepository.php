<?php

namespace App\Repositories\Driver;

use App\Models\Driver;
use App\Repositories\BaseRepository;
use App\Repositories\Driver\DriverInterface;

class DriverRepository extends BaseRepository implements DriverInterface
{
    /** Allowed option sets — mirror the drivers migration. */
    public const STATUSES = ['Active', 'Inactive', 'On Leave'];

    public function __construct(Driver $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'name'       => $request->name,
            'phone'      => $request->phone,
            'license_no' => $request->license_no,
            'vehicle'    => $request->vehicle,
            'status'     => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('license_no', 'like', "%{$search}%")
                    ->orWhere('vehicle', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
        ];
    }

    /** A driver with existing transport bookings must not be deleted. */
    protected function guardDelete($model): ?string
    {
        if ($model->bookings()->exists()) {
            return ___('alert.record_in_use_cannot_be_deleted');
        }

        return null;
    }
}
