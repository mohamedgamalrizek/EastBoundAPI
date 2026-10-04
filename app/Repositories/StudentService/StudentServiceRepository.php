<?php

namespace App\Repositories\StudentService;

use App\Models\StudentService;
use App\Repositories\BaseRepository;
use App\Repositories\StudentService\StudentServiceInterface;

class StudentServiceRepository extends BaseRepository implements StudentServiceInterface
{
    public const SERVICE_TYPE = ['University Admission', 'Offer Letter', 'Student Visa', 'Accommodation'];
    public const STATUS = ['Pending', 'Processing', 'Completed', 'Rejected'];

    protected array $with = ['customer'];

    public function __construct(StudentService $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'customer_id' => $request->customer_id ?: null,
            'student_name' => $request->student_name,
            'university' => $request->university,
            'country' => $request->country,
            'service_type' => $request->service_type,
            'status' => $request->status,
            'service_fee' => $request->filled('service_fee') ? $request->service_fee : null,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('university', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
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
