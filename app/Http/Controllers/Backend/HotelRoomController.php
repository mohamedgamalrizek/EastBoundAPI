<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\HotelRoom\HotelRoomInterface;
use App\Http\Requests\HotelRoom\StoreHotelRoomRequest;
use App\Http\Requests\HotelRoom\UpdateHotelRoomRequest;

class HotelRoomController extends BaseCrudController
{
    protected string $viewPath      = 'backend.hotel.room';
    protected string $redirectRoute = 'hotel.room.index';

    protected ?array $listAnalyticsConfig = ['model' => 'HotelRoom', 'group' => 'status', 'label' => 'Rooms'];

    public function __construct(HotelRoomInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreHotelRoomRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateHotelRoomRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }
}
