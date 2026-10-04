<?php

namespace App\Repositories\CorporateTravel;

use App\Models\CorporateTravel;
use App\Repositories\BaseRepository;
use App\Repositories\CorporateTravel\CorporateTravelInterface;

class CorporateTravelRepository extends BaseRepository implements CorporateTravelInterface
{
    public const SERVICE_TYPE = ['Employee Travel', 'Corporate Ticketing', 'Hotel Reservation', 'Business Tour'];
    public const STATUS = ['active', 'inactive'];

    protected array $with = ['customer'];

    public function __construct(CorporateTravel $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'customer_id' => $request->customer_id ?: null,
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'service_type' => $request->service_type,
            'employees' => $request->employees,
            'budget' => $request->budget,
            'status' => $request->status,
            'service_fee' => $request->filled('service_fee') ? $request->service_fee : null,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
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
            'service_type_options' => self::SERVICE_TYPE,
            'status_options' => self::STATUS,
        ];
    }
}
