<?php

namespace App\Repositories\EventBooking;

use App\Models\Customer;
use App\Models\EventBooking;
use App\Models\EventTour;
use App\Repositories\BaseRepository;
use App\Repositories\EventBooking\EventBookingInterface;

class EventBookingRepository extends BaseRepository implements EventBookingInterface
{
    /** Allowed option set — mirror the event_bookings migration. */
    public const STATUSES = ['Pending', 'Confirmed', 'Paid', 'Cancelled'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['eventTour', 'customer'];

    public function __construct(EventBooking $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'booking_no'     => $request->booking_no,
            'event_tour_id'  => $request->event_tour_id,
            'customer_id'    => $request->customer_id,
            'customer_name'  => $request->customer_name,
            'seats'          => $request->seats,
            'amount'         => $request->amount,
            'status'         => $request->status,
            'payment_method' => $request->payment_method,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhereHas('eventTour', fn ($e) => $e->where('title', 'like', "%{$search}%"));
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['event_tour_id'])) {
            $query->where('event_tour_id', $filters['event_tour_id']);
        }
    }

    public function formData(): array
    {
        return [
            'eventTours' => EventTour::orderBy('title')->get(['id', 'title']),
            'customers'  => Customer::orderBy('name')->get(['id', 'name']),
            'statuses'   => self::STATUSES,
            'methods'    => \App\Services\Accounting\BillingService::PAYMENT_METHODS,
        ];
    }
}
