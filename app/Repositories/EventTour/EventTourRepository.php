<?php

namespace App\Repositories\EventTour;

use App\Models\EventTour;
use App\Repositories\BaseRepository;
use App\Repositories\EventTour\EventTourInterface;

class EventTourRepository extends BaseRepository implements EventTourInterface
{
    public const TYPE = ['Conference', 'Trade Fair', 'Exhibition', 'Corporate Retreat'];
    public const STATUS = ['Open', 'Full', 'Closed'];

    public function __construct(EventTour $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'title' => $request->title,
            'type' => $request->type,
            'location' => $request->location,
            'event_date' => $request->event_date,
            'seats' => $request->seats,
            'status' => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'type_options' => self::TYPE,
            'status_options' => self::STATUS,
        ];
    }
}
