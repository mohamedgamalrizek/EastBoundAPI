<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\EventBooking\EventBookingInterface;
use App\Http\Requests\EventBooking\StoreEventBookingRequest;
use App\Http\Requests\EventBooking\UpdateEventBookingRequest;

class EventBookingController extends BaseCrudController
{
    protected string $viewPath      = 'backend.event-tour.booking';
    protected string $redirectRoute = 'event-tour.booking.index';

    protected ?array $listAnalyticsConfig = ['model' => 'EventBooking', 'group' => 'status', 'label' => 'Bookings'];

    public function __construct(EventBookingInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreEventBookingRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateEventBookingRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
