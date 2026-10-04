<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\FlightRoute\FlightRouteInterface;
use App\Http\Requests\FlightRoute\StoreFlightRouteRequest;
use App\Http\Requests\FlightRoute\UpdateFlightRouteRequest;

/**
 * Manages the published fare deals shown on /flight-booking.
 * Issued tickets are handled by FlightController.
 */
class FlightRouteController extends BaseCrudController
{
    protected string $viewPath      = 'backend.cms.flight-route';
    protected string $redirectRoute = 'cms.flight-route.index';

    // Grouped by trip_type (never null) rather than the nullable airline column.
    protected ?array $listAnalyticsConfig = ['model' => 'FlightRoute', 'group' => 'trip_type', 'label' => 'Fare deals'];

    public function __construct(FlightRouteInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreFlightRouteRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateFlightRouteRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
