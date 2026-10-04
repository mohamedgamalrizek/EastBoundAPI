<?php

namespace App\Repositories\TransportService;

use App\Models\TransportService;
use App\Repositories\BaseRepository;
use App\Http\Controllers\FrontendController;

class TransportServiceRepository extends BaseRepository implements TransportServiceInterface
{
    public const STATUSES = ['active', 'inactive'];

    /** A short, curated icon set so admins don't need Font Awesome knowledge. */
    public const ICONS = [
        'fa-plane-arrival'   => 'Airport pickup',
        'fa-plane-departure' => 'Airport drop',
        'fa-car-side'        => 'Car',
        'fa-van-shuttle'     => 'Van / shuttle',
        'fa-bus'             => 'Coach / bus',
        'fa-taxi'            => 'Taxi',
        'fa-truck-fast'      => 'Logistics',
    ];

    public function __construct(TransportService $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'title'        => $request->title,
            'icon'         => $request->icon,
            'description'  => $request->description,
            'price_from'   => $request->price_from ?? 0,
            'price_unit'   => $request->price_unit,
            'booking_type' => $request->booking_type,
            'vehicle_type' => $request->vehicle_type ?: null,
            'sort_order'   => $request->sort_order ?? 0,
            'status'       => $request->status,
        ];
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('title', 'like', "%{$filters['search']}%"))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('sort_order')->orderBy('title')->get();
    }

    public function formData(): array
    {
        return [
            'statuses' => self::STATUSES,
            'icons'    => self::ICONS,
            'vehicleTypes' => TransportService::VEHICLE_TYPES,
            // The public "Book now" button routes to /book/{type}; offering the
            // real list keeps admin-entered values valid links.
            'bookingTypes' => collect(FrontendController::bookingTypes())
                ->map(fn ($cfg) => $cfg['label'])->all(),
        ];
    }
}
