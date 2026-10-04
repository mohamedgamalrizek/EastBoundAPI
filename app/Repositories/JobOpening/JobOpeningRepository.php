<?php

namespace App\Repositories\JobOpening;

use App\Models\JobOpening;
use App\Repositories\BaseRepository;

class JobOpeningRepository extends BaseRepository implements JobOpeningInterface
{
    public const STATUSES         = ['active', 'inactive'];
    public const EMPLOYMENT_TYPES = ['Full-time', 'Part-time', 'Contract', 'Seasonal', 'Internship'];

    public function __construct(JobOpening $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'title'           => $request->title,
            'department'      => $request->department,
            'location'        => $request->location,
            'employment_type' => $request->employment_type,
            'description'     => $request->description,
            'closing_date'    => $request->closing_date,
            'sort_order'      => $request->sort_order ?? 0,
            'status'          => $request->status,
        ];
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('title', 'like', "%{$filters['search']}%"))
            ->when(filled($filters['department'] ?? null), fn ($q) => $q->where('department', $filters['department']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('sort_order')->orderBy('title')->get();
    }

    public function formData(): array
    {
        return [
            'statuses'        => self::STATUSES,
            'employmentTypes' => self::EMPLOYMENT_TYPES,
        ];
    }
}
