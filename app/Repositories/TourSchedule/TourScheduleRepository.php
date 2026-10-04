<?php

namespace App\Repositories\TourSchedule;

use App\Models\Package;
use App\Models\TourSchedule;
use App\Repositories\BaseRepository;
use App\Repositories\TourSchedule\TourScheduleInterface;

class TourScheduleRepository extends BaseRepository implements TourScheduleInterface
{
    /** Allowed option sets — mirror the tour_schedules migration. */
    public const STATUSES = ['open', 'full', 'closed'];

    /**
     * What an admin may actually pick. `full` is derived from seats
     * (see TourSchedule::getEffectiveStatusAttribute), never chosen by hand —
     * but it stays in STATUSES so existing rows keep validating.
     */
    public const SELECTABLE_STATUSES = ['open', 'closed'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['package'];

    public function __construct(TourSchedule $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'package_id'    => $request->package_id,
            'package_title' => $request->package_title,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'seats'         => $request->seats,
            'booked'        => $request->booked,
            'status'        => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('package_title', 'like', "%{$search}%");
            });
        }
        if (isset($filters['package_id'])) {
            $query->where('package_id', $filters['package_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'packages' => Package::orderBy('title')->get(['id', 'title']),
            'statuses' => self::SELECTABLE_STATUSES,
        ];
    }
}
