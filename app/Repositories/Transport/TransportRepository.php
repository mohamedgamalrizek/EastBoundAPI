<?php

namespace App\Repositories\Transport;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Role;
use App\Models\TransportBooking;
use App\Models\User;
use App\Models\VehicleCategory;
use App\Repositories\BaseRepository;
use App\Repositories\Transport\TransportInterface;

class TransportRepository extends BaseRepository implements TransportInterface
{
    /** Allowed option sets — mirror the transport_bookings migration.
     *  Pending = app request awaiting a fare; Paid = fare collected. */
    public const TYPES     = ['Bus', 'Train', 'Launch', 'Car', 'Airport'];
    public const STATUSES  = ['Pending', 'Booked', 'Confirmed', 'Paid', 'Completed', 'Cancelled'];
    /** Only meaningful when type is Airport — which way the passenger goes. */
    public const DIRECTIONS = ['Pickup', 'Drop'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer', 'booking.package', 'driver'];

    public function __construct(TransportBooking $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'   => $request->customer_id,
            'agent_id'      => $request->agent_id ?: null,
            'booking_id'    => $request->booking_id,
            'driver_id'     => $request->driver_id,
            'booking_no'    => $request->booking_no,
            'type'          => $request->type,
            // Only Airport actually uses this; every other type clears it,
            // so switching a booking away from Airport doesn't leave a stale
            // Pickup/Drop tag behind.
            'direction'     => $request->type === 'Airport' ? $request->direction : null,
            'customer_name' => $request->customer_name,
            'route'         => $request->route,
            'travel_date'   => $request->travel_date,
            'vehicle'       => $request->vehicle,
            'fare'          => $request->fare,
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
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('route', 'like', "%{$search}%");
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'customers'        => Customer::orderBy('name')->get(['id', 'name']),
            'agents'           => User::where('role_id', Role::where('name', 'Agent')->value('id'))
                                        ->orderBy('name')->get(['id', 'name', 'email']),
            'bookings'         => Booking::with('package')->latest()->get(['id', 'customer_name', 'package_id', 'travel_date']),
            'drivers'          => Driver::orderBy('name')->get(['id', 'name']),
            'types'            => self::TYPES,
            'directions'       => self::DIRECTIONS,
            'statuses'         => self::STATUSES,
            'methods'          => \App\Services\Accounting\BillingService::PAYMENT_METHODS,
            'vehicleCategories' => VehicleCategory::active()->ordered()->pluck('name'),
        ];
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    protected function byType(string $type)
    {
        return ['bookings' => TransportBooking::where('type', $type)->latest('travel_date')->get()];
    }

    public function bus()
    {
        return $this->byType('Bus');
    }

    public function train()
    {
        return $this->byType('Train');
    }

    public function launch()
    {
        return $this->byType('Launch');
    }

    public function car()
    {
        return $this->byType('Car');
    }

    public function airport()
    {
        return $this->byType('Airport');
    }

    public function reports()
    {
        return [
            'byType'    => TransportBooking::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'fareByType' => TransportBooking::selectRaw('type, sum(fare) as total')->groupBy('type')->pluck('total', 'type'),
            'totalTrips' => TransportBooking::count(),
            'totalFare'  => TransportBooking::sum('fare'),
        ];
    }
}
