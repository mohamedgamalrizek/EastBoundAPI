<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\HotelBooking\HotelBookingInterface;
use App\Http\Requests\HotelBooking\StoreHotelBookingRequest;
use App\Http\Requests\HotelBooking\UpdateHotelBookingRequest;

class HotelBookingController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hotel.booking';
    protected string $redirectRoute = 'hotel.booking.index';

    protected ?array $listAnalyticsConfig = ['model' => 'HotelBooking', 'group' => 'status', 'sum' => 'amount', 'label' => 'Bookings', 'sumLabel' => 'Total Amount'];

    public function __construct(HotelBookingInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreHotelBookingRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateHotelBookingRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
