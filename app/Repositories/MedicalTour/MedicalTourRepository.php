<?php

namespace App\Repositories\MedicalTour;

use App\Models\MedicalTour;
use App\Repositories\BaseRepository;
use App\Repositories\MedicalTour\MedicalTourInterface;

class MedicalTourRepository extends BaseRepository implements MedicalTourInterface
{
    public const STATUS = ['Inquiry', 'Confirmed', 'Completed', 'Cancelled'];

    protected array $with = ['customer'];

    public function __construct(MedicalTour $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'customer_id' => $request->customer_id ?: null,
            'patient_name' => $request->patient_name,
            'destination' => $request->destination,
            'hospital' => $request->hospital,
            'treatment' => $request->treatment,
            'cost' => $request->cost,
            'status' => $request->status,
            'service_fee' => $request->filled('service_fee') ? $request->service_fee : null,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('hospital', 'like', "%{$search}%")
                    ->orWhere('treatment', 'like', "%{$search}%");
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
            'status_options' => self::STATUS,
        ];
    }
}
