<?php

namespace App\Repositories\Branch;

use App\Models\Branch;
use App\Repositories\BaseRepository;
use App\Repositories\Branch\BranchInterface;

class BranchRepository extends BaseRepository implements BranchInterface
{
    public const STATUS = ['active', 'inactive'];

    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'name' => $request->name,
            'code' => $request->code,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'city' => $request->city,
            'address' => $request->address,
            'status' => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('manager_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'status_options' => self::STATUS,
        ];
    }
}
