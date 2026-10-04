<?php

namespace App\Repositories\VisaService;

use App\Models\VisaService;
use App\Repositories\BaseRepository;

class VisaServiceRepository extends BaseRepository implements VisaServiceInterface
{
    /** Allowed option sets — mirror the visa_services migration. */
    public const STATUSES = ['active', 'inactive'];
    public const TYPES    = ['Tourist', 'Business', 'Student', 'Work', 'Family', 'Umrah', 'Transit'];

    public function __construct(VisaService $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        $govtFee    = (float) ($request->govt_fee ?? 0);
        $serviceFee = (float) ($request->service_fee ?? 0);

        return [
            'country'         => $request->country,
            'flag'            => $request->flag,
            'visa_type'       => $request->visa_type,
            'processing_time' => $request->processing_time,
            'stay_duration'   => $request->stay_duration,
            'entry_type'      => $request->entry_type ?: 'Single',
            'govt_fee'        => $govtFee,
            'service_fee'     => $serviceFee,
            // Derived so every existing reader of `fee` shows the real total.
            'fee'             => $govtFee + $serviceFee,
            'requirements'    => $request->requirements,
            'is_featured'     => (bool) $request->is_featured,
            'sort_order'      => $request->sort_order ?? 0,
            'status'          => $request->status,
        ];
    }

    /**
     * Admin list follows the same order the public page uses, so re-ordering
     * with `sort_order` is visible immediately.
     */
    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('country', 'like', "%{$filters['search']}%"))
            ->when(filled($filters['visa_type'] ?? null), fn ($q) => $q->where('visa_type', $filters['visa_type']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('sort_order')->orderBy('country')->get();
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
            'types'    => self::TYPES,
        ];
    }
}
