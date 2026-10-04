<?php

namespace App\Repositories\HotelRoom;

use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Repositories\BaseRepository;
use App\Repositories\HotelRoom\HotelRoomInterface;

class HotelRoomRepository extends BaseRepository implements HotelRoomInterface
{
    /** Allowed option sets — mirror the hotel_rooms migration. */
    public const STATUSES = ['available', 'sold-out'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['hotel'];

    public function __construct(HotelRoom $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'hotel_id'        => $request->hotel_id,
            'room_type'       => $request->room_type,
            'capacity'        => $request->capacity,
            'rate_per_night'  => $request->rate_per_night,
            'total_rooms'     => $request->total_rooms,
            'available_rooms' => $request->available_rooms,
            'status'          => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('room_type', 'like', "%{$search}%");
            });
        }
        if (isset($filters['hotel_id'])) {
            $query->where('hotel_id', $filters['hotel_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'hotels'   => Hotel::orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ];
    }

    /** A room with existing bookings must not be deleted. */
    protected function guardDelete($model): ?string
    {
        if ($model->bookings()->exists()) {
            return ___('alert.record_in_use_cannot_be_deleted');
        }

        return null;
    }
}
