<?php

namespace App\Repositories\Hajj;

use App\Models\HajjFlight;
use App\Models\HajjGroup;
use App\Models\Hotel;
use App\Models\HajjPackage;
use App\Models\HajjPilgrim;
use App\Repositories\BaseRepository;
use App\Repositories\Hajj\HajjInterface;
use App\Repositories\HajjPilgrim\HajjPilgrimRepository;

class HajjRepository extends BaseRepository implements HajjInterface
{
    /** Allowed option sets — mirror the hajj_packages migration. */
    public const TYPES    = ['Hajj', 'Umrah'];
    public const STATUSES = ['active', 'inactive'];

    public function __construct(HajjPackage $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'package_no'    => $request->package_no,
            'title'         => $request->title,
            'type'          => $request->type,
            'duration_days' => $request->duration_days,
            'price'         => $request->price,
            'seats'         => $request->seats,
            'status'        => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('package_no', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
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
            'types'    => self::TYPES,
            'statuses' => self::STATUSES,
        ];
    }

    /** A package with existing pilgrims must not be deleted. */
    protected function guardDelete($model): ?string
    {
        if ($model->pilgrims()->exists()) {
            return ___('alert.record_in_use_cannot_be_deleted');
        }

        return null;
    }

    /* ---------------------------------------------------------------------
     | Management pages
     * ------------------------------------------------------------------- */

    public function hotelAllocation()
    {
        $pilgrims = HajjPilgrim::orderBy('group_name')->orderBy('name')->get();

        return [
            'pilgrims'      => $pilgrims,
            'allocated'     => $pilgrims->filter->hasHotels()->count(),
            'pending'       => $pilgrims->reject->hasHotels()->count(),
            'makkahHotels'  => $this->hotelsIn(['makkah', 'mecca']),
            'madinahHotels' => $this->hotelsIn(['madinah', 'medina']),
            'roomsByHotel'  => $this->roomsByHotel(),
        ];
    }

    /**
     * Hotels registered for a city, from the Hotel module — the allocation
     * screen should offer the properties the agency actually contracted, not
     * a free-text box where every clerk spells "Hilton Makkah" differently.
     *
     * If nobody has registered a hotel in that city yet, offer every active
     * hotel instead of an empty dropdown the office cannot get past.
     */
    private function hotelsIn(array $cityKeywords)
    {
        $hotels = Hotel::active()
            ->where(function ($q) use ($cityKeywords) {
                foreach ($cityKeywords as $city) {
                    $q->orWhere('city', 'like', "%{$city}%");
                }
            })
            ->orderBy('name')
            ->pluck('name');

        return $hotels->isNotEmpty()
            ? $hotels
            : Hotel::active()->orderBy('name')->pluck('name');
    }

    /**
     * hotel name => its room types, for the Room No suggestions.
     *
     * Rooms in the Hotel module are types with a capacity, not numbered doors,
     * so these are offered as suggestions next to a field the office can still
     * type a real room number into.
     */
    private function roomsByHotel(): array
    {
        return Hotel::active()
            ->with(['rooms' => fn ($q) => $q->orderBy('room_type')])
            ->get()
            ->mapWithKeys(fn ($hotel) => [
                $hotel->name => $hotel->rooms
                    ->map(fn ($room) => $room->room_type . ' (' . ___('label.capacity') . ' ' . $room->capacity . ')')
                    ->values()
                    ->all(),
            ])
            ->filter(fn ($rooms) => ! empty($rooms))
            ->all();
    }

    public function flightAllocation()
    {
        $pilgrims = HajjPilgrim::orderBy('group_name')->orderBy('name')->get();
        $flights  = HajjFlight::orderBy('flight_no')->get();

        // The seat map goes to the page as data, so picking a flight can swap
        // the seat list without a round trip. Seats already given out are
        // marked rather than hidden: the office needs to see that 12A is Abdul
        // Karim's, not just that it is missing.
        $seatMap = $flights->mapWithKeys(function (HajjFlight $flight) {
            $taken = $flight->takenSeats();

            return [$flight->flight_no => [
                'departure_date' => $flight->departure_date?->format('Y-m-d'),
                'return_date'    => $flight->return_date?->format('Y-m-d'),
                'seats'          => collect($flight->seats())
                    ->map(fn ($seat) => ['seat' => $seat, 'taken_by' => $taken[$seat] ?? null])
                    ->values()
                    ->all(),
            ]];
        });

        return [
            'pilgrims'  => $pilgrims,
            'flights'   => $flights,
            'seatMap'   => $seatMap,
            'allocated' => $pilgrims->filter->hasFlight()->count(),
            'pending'   => $pilgrims->reject->hasFlight()->count(),
        ];
    }

    /** Add a flight the batch will travel on, with its seat map. */
    public function storeFlight($request)
    {
        try {
            $flightNo = trim((string) $request->flight_no);

            if (HajjFlight::where('flight_no', $flightNo)->exists()) {
                return $this->responseWithError(___('alert.flight_already_exists'));
            }

            HajjFlight::create([
                'flight_no'      => $flightNo,
                'airline'        => $request->airline ?: null,
                'departure_date' => $request->departure_date ?: null,
                'return_date'    => $request->return_date ?: null,
                'seat_rows'      => (int) ($request->seat_rows ?: 30),
                'seat_letters'   => strtoupper($request->seat_letters ?: 'ABCDEF'),
            ]);

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /**
     * Remove a flight nobody is booked on. One with pilgrims stays: deleting
     * it would leave them holding a flight number that no longer exists.
     */
    public function deleteFlight($id)
    {
        try {
            $flight = HajjFlight::find($id);

            if (! $flight) {
                return $this->responseWithError(___('alert.not_found'));
            }

            if ($flight->pilgrims()->exists()) {
                return $this->responseWithError(___('alert.flight_in_use'));
            }

            $flight->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /**
     * Groups are how a hajj batch is actually run — one leader, one bus, one
     * hotel block. The page lists each group with its roster, plus the
     * pilgrims not yet placed in one.
     */
    public function groups()
    {
        $pilgrims = HajjPilgrim::orderBy('group_name')->orderBy('name')->get();
        $members  = $pilgrims->filter(fn ($p) => filled($p->group_name))->groupBy('group_name');

        // The catalogue drives the screen, not the pilgrims: a group the office
        // has just created has nobody in it yet and still has to be visible and
        // pickable. Any name a pilgrim carries is registered on the way in, so
        // the catalogue cannot be missing one.
        $catalogue = HajjGroup::orderBy('name')->get();

        return [
            'catalogue'  => $catalogue,
            'groups'     => $catalogue->mapWithKeys(
                fn ($group) => [$group->name => $members->get($group->name, collect())]
            ),
            'unassigned' => $pilgrims->filter(fn ($p) => blank($p->group_name))->values(),
            'groupNames' => $catalogue->pluck('name'),
        ];
    }

    /**
     * Create a group so it can be filled afterwards. Names are unique: two
     * groups called "Group A" would be indistinguishable on every screen that
     * shows a pilgrim's group.
     */
    public function storeGroup($request)
    {
        try {
            $name = trim((string) $request->name);

            if (HajjGroup::where('name', $name)->exists()) {
                return $this->responseWithError(___('alert.group_already_exists'));
            }

            HajjGroup::create([
                'name'   => $name,
                'leader' => $request->leader ?: null,
                'notes'  => $request->notes ?: null,
            ]);

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /**
     * Remove an empty group. A group with pilgrims in it is left alone —
     * deleting it would silently unassign people from their bus and hotel
     * block, which is not what "remove this group from the list" means.
     */
    public function deleteGroup($id)
    {
        try {
            $group = HajjGroup::find($id);

            if (! $group) {
                return $this->responseWithError(___('alert.not_found'));
            }

            if ($group->pilgrims()->exists()) {
                return $this->responseWithError(___('alert.group_not_empty'));
            }

            $group->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function payments()
    {
        $pilgrims = HajjPilgrim::latest()->get();

        return [
            'pilgrims'    => $pilgrims,
            'collected'   => (float) $pilgrims->sum('amount_paid'),
            'outstanding' => (float) $pilgrims->sum('amount_due'),
            'fullyPaid'   => $pilgrims->where('payment_status', 'Paid')->count(),
        ];
    }

    /**
     * Assign hotels, a flight, a group or a payment against one pilgrim.
     *
     * All four allocation screens post here: they edit different columns of the
     * same row, and splitting them into four near-identical methods would only
     * duplicate the lookup and the logging.
     */
    public function allocate($request, $id)
    {
        try {
            $pilgrim = HajjPilgrim::find($id);

            if (! $pilgrim) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $pilgrim->fill($request->only([
                'makkah_hotel', 'madinah_hotel', 'room_no',
                'flight_no', 'departure_date', 'return_date', 'seat_no',
                'group_name',
            ]));

            // Normally the name came from the group picker and already exists;
            // this keeps the catalogue right when it did not (import, API).
            if ($request->filled('group_name')) {
                HajjGroup::register($request->input('group_name'));
            }

            if ($request->filled('flight_no')) {
                HajjFlight::register($request->input('flight_no'));
            }

            // Two pilgrims in one seat is the mistake the free-text seat box
            // used to allow, and it only surfaces at the airport.
            if (filled($pilgrim->flight_no) && filled($pilgrim->seat_no)) {
                $clash = HajjPilgrim::where('flight_no', $pilgrim->flight_no)
                    ->where('seat_no', $pilgrim->seat_no)
                    ->whereKeyNot($pilgrim->id)
                    ->first();

                if ($clash) {
                    return $this->responseWithError(
                        "Seat {$pilgrim->seat_no} on {$pilgrim->flight_no} is already {$clash->name}'s."
                    );
                }
            }

            // A payment is an amount received now, not a new running total.
            // Saving is all this has to do: HajjPilgrimObserver mirrors the
            // new amount_paid into the invoice and writes the receipt, in the
            // method recorded here.
            if ($request->filled('payment')) {
                $payment = max(0, (float) $request->input('payment'));
                $pilgrim->amount_paid = (float) $pilgrim->amount_paid + $payment;
                $pilgrim->amount_due  = max(0, (float) $pilgrim->amount_due - $payment);
                $pilgrim->paymentMethodHint = $request->input('method') ?: 'Cash';
                $pilgrim->syncPaymentStatus();
            }

            if ($request->filled('amount_due')) {
                $pilgrim->amount_due = (float) $request->input('amount_due');
                $pilgrim->syncPaymentStatus();
            }

            if ($request->filled('document_status')) {
                $pilgrim->document_status = $request->input('document_status');
            }

            $pilgrim->save();
            $this->logActivity('updated', $pilgrim);

            return $this->responseWithSuccess("{$pilgrim->name} updated.");
        } catch (\Throwable $th) {
            return $this->responseWithError('Unable to update the pilgrim.');
        }
    }

    public function documents()
    {
        return [
            'pilgrims'         => HajjPilgrim::latest()->get(),
            'documentStatuses' => HajjPilgrimRepository::DOCUMENT_STATUSES,
        ];
    }

    /**
     * Move a pilgrim's document check along from the verification screen.
     *
     * The page is where the work actually happens, so the status is set here
     * rather than only inside the full pilgrim edit form.
     */
    public function updateDocumentStatus($id, string $status)
    {
        try {
            if (! in_array($status, HajjPilgrimRepository::DOCUMENT_STATUSES, true)) {
                return $this->responseWithError(___('alert.something_went_wrong'));
            }

            $pilgrim = HajjPilgrim::find($id);

            if (! $pilgrim) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $pilgrim->document_status = $status;
            $pilgrim->save();

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function reports()
    {
        return [
            'totalPilgrims'  => HajjPilgrim::count(),
            'totalPackages'  => HajjPackage::count(),
            'totalGroups'    => HajjPilgrim::whereNotNull('group_name')->distinct('group_name')->count('group_name'),
            'docsVerified'   => HajjPilgrim::where('document_status', 'Verified')->count(),
            'fullyPaid'      => HajjPilgrim::where('payment_status', 'Paid')->count(),
            'byPackage'      => HajjPilgrim::selectRaw('package_title, count(*) as total')->groupBy('package_title')->pluck('total', 'package_title'),
            'byStatus'       => HajjPilgrim::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ];
    }
}
