<?php

namespace App\Http\Controllers\Backend;

use App\Models\TourSchedule;
use Illuminate\Http\Request;
use App\Repositories\TourSchedule\TourScheduleInterface;
use App\Http\Requests\TourSchedule\StoreTourScheduleRequest;
use App\Http\Requests\TourSchedule\UpdateTourScheduleRequest;
use App\Http\Controllers\Backend\Concerns\BuildsListAnalytics;

class TourScheduleController extends BaseCrudController
{
    use BuildsListAnalytics;

    protected string $viewPath      = 'backend.tour.schedule';
    protected string $redirectRoute = 'tour.schedule.index';

    public function __construct(TourScheduleInterface $repo)
    {
        $this->repo = $repo;
    }

    protected function listAnalytics(Request $request): ?array
    {
        return [
            'stats' => [
                'Total Schedules' => TourSchedule::count(),
                'Open'            => TourSchedule::where('status', 'open')->count(),
                'Total Seats'     => number_format((int) TourSchedule::sum('seats')),
                'Booked'          => number_format((int) TourSchedule::sum('booked')),
            ],
            'donut' => $this->laGroup(TourSchedule::class, 'status'),
            'trend' => $this->laMonthly(TourSchedule::class, 'Schedules', 'COUNT(*)', 'bar', 'start_date'),
        ];
    }

    public function store(StoreTourScheduleRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTourScheduleRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
