<?php

namespace App\Repositories\HotelBooking;

use App\Models\Hotel;
use App\Models\Customer;
use App\Models\HotelRoom;
use App\Models\HotelBooking;
use App\Models\Role;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\HotelBooking\HotelBookingInterface;

class HotelBookingRepository extends BaseRepository implements HotelBookingInterface
{
    /** Allowed option sets — mirror the hotel_bookings migration. */
    public const STATUSES = ['Booked', 'Confirmed', 'Paid', 'Cancelled'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['hotel', 'customer', 'hotelRoom'];

    public function __construct(HotelBooking $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'booking_no'    => $request->booking_no,
            'hotel_id'      => $request->hotel_id,
            'customer_id'   => $request->customer_id,
            'agent_id'      => $request->agent_id ?: null,
            'hotel_room_id' => $request->hotel_room_id,
            'guest_name'    => $request->guest_name,
            'check_in'      => $request->check_in,
            'check_out'     => $request->check_out,
            'nights'        => $request->nights,
            'amount'        => $request->amount,
            'status'        => $request->status,
            'payment_method' => $request->payment_method,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                    ->orWhere('guest_name', 'like', "%{$search}%")
                    ->orWhereHas('hotelRoom', fn ($r) => $r->where('room_type', 'like', "%{$search}%"));
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['hotel_id'])) {
            $query->where('hotel_id', $filters['hotel_id']);
        }
    }

    public function formData(): array
    {
        return [
            'hotels'    => Hotel::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'agents'    => User::where('role_id', Role::where('name', 'Agent')->value('id'))
                                ->orderBy('name')->get(['id', 'name', 'email']),
            'rooms'     => HotelRoom::get(['id', 'room_type', 'hotel_id']),
            'statuses'  => self::STATUSES,
            'methods'   => \App\Services\Accounting\BillingService::PAYMENT_METHODS,
        ];
    }
}
