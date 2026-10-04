<?php

namespace App\Repositories\FlightRoute;

use App\Models\FlightRoute;
use App\Repositories\BaseRepository;

class FlightRouteRepository extends BaseRepository implements FlightRouteInterface
{
    /** Allowed option sets — mirror the flight_routes migration. */
    public const STATUSES   = ['active', 'inactive'];
    public const TRIP_TYPES = ['One-way', 'Round-trip'];

    public function __construct(FlightRoute $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'origin'           => $request->origin,
            'origin_code'      => $request->origin_code,
            'destination'      => $request->destination,
            'destination_code' => $request->destination_code,
            'airline'          => $request->airline,
            'fare'             => $request->fare ?? 0,
            'trip_type'        => $request->trip_type,
            'is_featured'      => (bool) $request->is_featured,
            'sort_order'       => $request->sort_order ?? 0,
            'status'           => $request->status,
        ];
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['search'] ?? null), function ($q) use ($filters) {
                $q->where(function ($w) use ($filters) {
                    $w->where('origin', 'like', "%{$filters['search']}%")
                      ->orWhere('destination', 'like', "%{$filters['search']}%")
                      ->orWhere('airline', 'like', "%{$filters['search']}%");
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('sort_order')->orderBy('fare')->get();
    }

    public function formData(): array
    {
        return [
            'statuses'  => self::STATUSES,
            'tripTypes' => self::TRIP_TYPES,
        ];
    }
}
