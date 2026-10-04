<?php

namespace App\Repositories\HotelRoom;

interface HotelRoomInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);
}
