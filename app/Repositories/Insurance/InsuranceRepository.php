<?php

namespace App\Repositories\Insurance;

use App\Models\Insurance;
use App\Repositories\BaseRepository;
use App\Repositories\Insurance\InsuranceInterface;

class InsuranceRepository extends BaseRepository implements InsuranceInterface
{
    public const TYPE = ['Single Trip', 'Multi Trip'];
    public const STATUS = ['active', 'inactive'];

    protected array $with = ['customer'];

    public function __construct(Insurance $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'customer_id' => $request->customer_id ?: null,
            'provider' => $request->provider,
            'plan_name' => $request->plan_name,
            'type' => $request->type,
            'coverage' => $request->coverage,
            'premium' => $request->premium,
            'status' => $request->status,
            'service_fee' => $request->filled('service_fee') ? $request->service_fee : null,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', "%{$search}%")
                    ->orWhere('plan_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'customers' => \App\Models\Customer::orderBy('name')->get(['id', 'name']),
            'type_options' => self::TYPE,
            'status_options' => self::STATUS,
        ];
    }
}
