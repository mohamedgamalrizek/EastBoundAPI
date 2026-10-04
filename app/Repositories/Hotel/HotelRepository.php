<?php

namespace App\Repositories\Hotel;

use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\HotelBooking;
use App\Repositories\BaseRepository;
use App\Repositories\Concerns\StoresPublicImage;
use App\Repositories\Hotel\HotelInterface;

class HotelRepository extends BaseRepository implements HotelInterface
{
    use StoresPublicImage;

    /** Allowed option sets — mirror the hotels migration. */
    public const STATUSES   = ['active', 'inactive'];
    public const CATEGORIES = [1, 2, 3, 4, 5];

    public function __construct(Hotel $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'name'            => $request->name,
            'city'            => $request->city,
            'country'         => $request->country,
            'category'        => $request->category,
            'image'       => $this->resolveImage($request, 'image', 'image_url', 'hotels', $this->currentImage($request, 'image')),
            'description'     => $request->description,
            'is_featured'     => (bool) $request->is_featured,
            'rooms_count'     => $request->rooms_count,
            'price_per_night' => $request->price_per_night,
            'status'          => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
    }

    public function formData(): array
    {
        return [
            'statuses'   => self::STATUSES,
            'categories' => self::CATEGORIES,
        ];
    }

    /** A hotel with existing rooms or bookings must not be deleted. */
    protected function guardDelete($model): ?string
    {
        if ($model->rooms()->exists() || $model->bookings()->exists()) {
            return ___('alert.record_in_use_cannot_be_deleted');
        }

        return null;
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function details($id)
    {
        return ['hotel' => Hotel::find($id)];
    }

    public function availability()
    {
        return ['rooms' => HotelRoom::with('hotel')->orderByDesc('available_rooms')->get()];
    }

    public function vouchers()
    {
        // Vouchers are issued only for confirmed bookings.
        return ['bookings' => HotelBooking::with(['hotel', 'hotelRoom'])->where('status', 'Confirmed')->latest('check_in')->get()];
    }

    public function reports()
    {
        return [
            'totalHotels'   => Hotel::count(),
            'activeCount'   => Hotel::where('status', 'active')->count(),
            'totalRooms'    => (int) Hotel::sum('rooms_count'),
            'avgPrice'      => (float) Hotel::avg('price_per_night'),
            'roomTypes'     => (int) HotelRoom::count(),
            'totalBookings' => (int) HotelBooking::count(),
            'byCity'        => Hotel::selectRaw('city, count(*) as total')->groupBy('city')->pluck('total', 'city'),
            'byCategory'    => Hotel::selectRaw('category, count(*) as total')->groupBy('category')->orderBy('category', 'desc')->pluck('total', 'category'),
            'hotels'        => Hotel::orderByDesc('price_per_night')->get(),
        ];
    }
}
