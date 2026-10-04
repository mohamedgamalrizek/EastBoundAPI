<?php

namespace App\Repositories\VehicleCategory;

use App\Models\VehicleCategory;
use App\Repositories\BaseRepository;

class VehicleCategoryRepository extends BaseRepository implements VehicleCategoryInterface
{
    public const STATUSES = ['active', 'inactive'];

    public function __construct(VehicleCategory $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status,
        ];
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('name', 'like', "%{$filters['search']}%"))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->ordered()->get();
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
        ];
    }
}
