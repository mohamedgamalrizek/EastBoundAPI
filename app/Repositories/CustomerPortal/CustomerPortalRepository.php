<?php

namespace App\Repositories\CustomerPortal;

use App\Repositories\CustomerPortal\CustomerPortalInterface;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\HotelRoom;
use App\Models\Package;
use App\Models\SupportTicket;
use App\Models\Traveler;
use App\Models\TransportBooking;
use App\Models\Passport;
use App\Models\Wishlist;
use App\Services\Accounting\CustomerWalletService;
use App\Services\Booking\BookingPricing;
use App\Services\LoyaltyService;
use App\Services\Payments\PaymentIntentService;
use App\Services\Payments\PaymentManager;
use App\Services\ReviewService;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Carbon;

class CustomerPortalRepository implements CustomerPortalInterface
{
    use ReturnFormatTrait;

    /** Allowed option sets for the customer's own records. */
    public const TRAVELER_RELATIONS = ['Self', 'Spouse', 'Child', 'Parent'];
    public const TRAVELER_STATUSES  = ['Active', 'Inactive'];
    public const PASSPORT_STATUSES  = [
        Passport::STATUS_VALID,
        Passport::STATUS_EXPIRING,
        Passport::STATUS_EXPIRED,
    ];
    public const TICKET_PRIORITIES  = ['Low', 'Medium', 'High'];
    public const TICKET_DEPARTMENTS = ['General', 'Booking', 'Visa', 'Payment', 'Complaint'];

    /** Methods a customer can pay with from the portal (record-payment). */
    public const PAY_METHODS = ['Wallet', 'bKash', 'Nagad', 'Card', 'Cash', 'Bank'];

    /**
     * Resolve the Customer record for the logged-in portal user, creating a
     * matching one (by email) on first use. Every self-service action is scoped
     * to this customer, so a portal user can only ever see/edit their own data.
     */
    protected function currentCustomer(): Customer
    {
        $user = auth()->user();

        return Customer::firstOrCreate(
            ['email' => $user->email],
            ['name' => $user->name, 'phone' => $user->phone, 'tier' => 'Silver', 'status' => 'active']
        );
    }

    public function dashboard()
    {
        $customer = $this->currentCustomer();

        return [
            'bookingCount'  => $customer->bookings()->count(),
            'upcomingCount' => $customer->bookings()->whereIn('status', ['pending', 'confirmed'])->count(),
            'walletBalance' => $this->walletBalance($customer),
            'visaCount'     => $customer->visaApplications()->count(),
            'recent'        => $customer->bookings()->latest()->take(8)->get(),
        ];
    }

    /* ---------------------------------------------------------------------
     | Travelers — customer self-service (scoped to the current customer)
     * ------------------------------------------------------------------- */

    public function travelers()
    {
        return [
            'travelers' => $this->currentCustomer()->travelers()->latest()->get(),
            'relations' => self::TRAVELER_RELATIONS,
            'statuses'  => self::TRAVELER_STATUSES,
        ];
    }

    public function findOwnTraveler($id): Traveler
    {
        return $this->currentCustomer()->travelers()->findOrFail($id);
    }

    public function storeTraveler($request)
    {
        try {
            $this->currentCustomer()->travelers()->create($this->travelerData($request));

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function updateTraveler($request)
    {
        try {
            $this->findOwnTraveler($request->id)->update($this->travelerData($request));

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function deleteTraveler($id)
    {
        try {
            $this->findOwnTraveler($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    private function travelerData($request): array
    {
        return [
            'name'        => $request->name,
            'relation'    => $request->relation,
            'passport_no' => $request->passport_no ?: null,
            'nationality' => $request->nationality ?: null,
            'dob'         => $request->dob ?: null,
            'status'      => $request->status,
        ];
    }

    /* ---------------------------------------------------------------------
     | Passports — customer self-service (scoped to the current customer)
     * ------------------------------------------------------------------- */

    public function passport()
    {
        return [
            'passports' => $this->currentCustomer()->passports()->latest()->get(),
            'statuses'  => self::PASSPORT_STATUSES,
        ];
    }

    public function findOwnPassport($id): Passport
    {
        return $this->currentCustomer()->passports()->findOrFail($id);
    }

    public function storePassport($request)
    {
        try {
            $this->currentCustomer()->passports()->create($this->passportData($request));

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function updatePassport($request)
    {
        try {
            $this->findOwnPassport($request->id)->update($this->passportData($request));

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function deletePassport($id)
    {
        try {
            $this->findOwnPassport($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    private function passportData($request): array
    {
        return [
            'holder_name' => $request->holder_name,
            'passport_no' => $request->passport_no,
            'nationality' => $request->nationality ?: null,
            'issue_date'  => $request->issue_date ?: null,
            'expiry_date' => $request->expiry_date ?: null,
        ];
    }

    public function bookings()
    {
        $customer = $this->currentCustomer();
        $reviews  = app(ReviewService::class);

        return [
            'bookings' => $customer->bookings()->with(['package', 'review'])->latest()->get(),
            'methods'  => self::PAY_METHODS,
            // Per booking: the review already written, or why one cannot be
            // yet. Worked out here so the blade stays a template.
            'reviewState' => $customer->bookings()->with('review')->get()
                ->mapWithKeys(fn (Booking $b) => [$b->id => [
                    'review' => $b->review,
                    'reason' => $reviews->rejectionReason($b, $customer),
                ]])->all(),
        ];
    }

    /** Public catalogue — not customer data, so intentionally unscoped. */
    public function tours()
    {
        $customer = $this->currentCustomer();
        $loyalty  = app(LoyaltyService::class);

        return [
            // approvedReviews is eager-loaded because Package::$avg_rating and
            // $reviews_count read from the relation when it is there, and fall
            // back to a query per package when it is not.
            'packages' => Package::where('status', 'active')->with('approvedReviews')->latest()->get(),
            // What the Book modal needs to offer a discount: the balance, and
            // the rules that govern spending it.
            'points'   => [
                'balance'     => $loyalty->balance($customer),
                'rate'        => $loyalty->redeemRate(),
                'min'         => $loyalty->minRedeem(),
                'max_percent' => $loyalty->maxRedeemPercent(),
            ],
        ];
    }

    /* ---------------------------------------------------------------------
     | Self-service booking & payment
     |
     | The portal writes the same rows the mobile app writes; the observers
     | do the rest (invoice on confirmation, receipt on payment). Paying is
     | the app's record-payment flow: pick a method, the books follow. A
     | Wallet payment needs the balance to actually be there.
     * ------------------------------------------------------------------- */

    public function bookTour($request)
    {
        try {
            $customer = $this->currentCustomer();
            $package  = Package::where('status', 'active')->find($request->package_id);

            if (! $package) {
                return $this->responseWithError('This tour is not available.');
            }

            $pricing = app(BookingPricing::class);
            $quote   = $pricing->quote(
                (float) $package->price * (int) $request->travelers,
                $customer,
                $request->coupon_code,
                (int) $request->input('points_redeemed', 0),
            );

            $booking = Booking::create([
                'customer_id'    => $customer->id,
                'package_id'     => $package->id,
                'customer_name'  => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'travel_date'    => $request->travel_date,
                'travelers'      => (int) $request->travelers,
                'status'         => 'pending',
            ] + $pricing->columns($quote));

            // A coupon that could not be used never blocks the booking; the
            // customer is told why it was not applied and keeps the trip.
            $notes = array_filter([
                $quote['coupon_error'],
                $quote['points_error'],
                ...$pricing->commit($booking, $quote),
            ]);

            return $this->responseWithSuccess(trim(
                'Booking placed — pay now or at the desk. ' . implode(' ', $notes)
            ));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /**
     * Price a tour before anything is saved — what the Book modal's "Apply"
     * button calls so the customer sees the discount before committing.
     */
    public function quoteTour($request): array
    {
        $customer = $this->currentCustomer();
        $package  = Package::where('status', 'active')->find($request->package_id);

        if (! $package) {
            return ['error' => 'This tour is not available.'];
        }

        return app(BookingPricing::class)->preview(
            (float) $package->price * max(1, (int) $request->travelers),
            $customer,
            $request->coupon_code,
            (int) $request->input('points_redeemed', 0),
        );
    }

    /* ---------------------------------------------------------------------
     | Reviews — a verdict a customer may only leave on a trip they paid for
     | and have now taken. ReviewService owns that rule; this scopes it to
     | whoever is logged in.
     * ------------------------------------------------------------------- */

    public function reviews(): array
    {
        $customer = $this->currentCustomer();
        $service  = app(ReviewService::class);

        return [
            'reviews'  => $customer->reviews()->with('package')->latest()->get(),
            'pending'  => $service->awaitingReview($customer),
        ];
    }

    public function storeReview($request)
    {
        try {
            $customer = $this->currentCustomer();
            $booking  = $customer->bookings()->find($request->booking_id);

            if (! $booking) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $service = app(ReviewService::class);

            if ($reason = $service->rejectionReason($booking, $customer)) {
                return $this->responseWithError($reason);
            }

            $service->create($booking, $customer, (int) $request->rating, $request->title, $request->comment);

            return $this->responseWithSuccess($service->autoApproves()
                ? 'Thanks — your review is now live.'
                : 'Thanks — your review will appear once it has been checked.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function payBooking($request)
    {
        try {
            $booking = $this->currentCustomer()->bookings()->find($request->id);

            if (! $booking) {
                return $this->responseWithError(___('alert.not_found'));
            }
            if ($booking->status === 'paid') {
                return $this->responseWithError('This booking is already paid.');
            }
            if ($booking->status === 'cancelled') {
                return $this->responseWithError('A cancelled booking cannot be paid.');
            }

            if ($error = $this->walletShortfall($request->method, (float) $booking->amount)) {
                return $error;
            }

            // The customer declares a payment; only the agency can confirm one.
            //
            // Marking the booking paid here ran BookingObserver, which issues a
            // receipt and credits the agent's commission — real money leaving
            // the books on nothing but the customer's word. The declaration is
            // recorded and the desk marks it paid once the money is actually in.
            $this->recordPaymentClaim($booking, (string) $request->method);

            return $this->responseWithSuccess(
                'Payment submitted — we will confirm it shortly and update your booking.'
            );
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function bookHotel($request)
    {
        try {
            $customer = $this->currentCustomer();
            $room     = HotelRoom::where('hotel_id', $request->hotel_id)->find($request->hotel_room_id);

            if (! $room) {
                return $this->responseWithError('That room does not belong to the selected hotel.');
            }

            $nights = max(1, Carbon::parse($request->check_in)->diffInDays(Carbon::parse($request->check_out)));

            $customer->hotelBookings()->create([
                'booking_no'    => 'HTL-' . strtoupper(substr(uniqid(), -6)),
                'hotel_id'      => $request->hotel_id,
                'hotel_room_id' => $room->id,
                'guest_name'    => $request->guest_name ?: $customer->name,
                'check_in'      => $request->check_in,
                'check_out'     => $request->check_out,
                'nights'        => $nights,
                'amount'        => $nights * (float) $room->rate_per_night,
                'status'        => 'Booked',
            ]);

            return $this->responseWithSuccess('Hotel booked — the desk will confirm it shortly.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function payHotel($request)
    {
        try {
            $booking = $this->currentCustomer()->hotelBookings()->find($request->id);

            if (! $booking) {
                return $this->responseWithError(___('alert.not_found'));
            }
            if ($booking->status === 'Paid') {
                return $this->responseWithError('This booking is already paid.');
            }
            if ($booking->status === 'Cancelled') {
                return $this->responseWithError('A cancelled booking cannot be paid.');
            }

            if ($error = $this->walletShortfall($request->method, (float) $booking->amount)) {
                return $error;
            }

            // Same rule as a package booking: the customer declares, the desk confirms.
            app(\App\Actions\RecordPaymentClaim::class)(
                $booking,
                $booking->booking_no ?: 'Hotel booking #' . $booking->id,
                (string) ($booking->guest_name ?? $this->currentCustomer()->name),
                (float) $booking->amount,
                (string) $request->method
            );

            return $this->responseWithSuccess(
                'Payment submitted — we will confirm it shortly and update your booking.'
            );
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function requestTransport($request)
    {
        try {
            $customer = $this->currentCustomer();

            $customer->transportBookings()->create([
                'booking_no'    => 'TRP-' . strtoupper(substr(uniqid(), -6)),
                'type'          => $request->type,
                'customer_name' => $customer->name,
                'route'         => $request->route,
                'travel_date'   => $request->travel_date,
                'vehicle'       => $request->vehicle,
                'fare'          => 0, // priced by the agency on confirmation
                'status'        => 'Pending',
            ]);

            return $this->responseWithSuccess('Request received — we will confirm the fare shortly.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function payTransport($request)
    {
        try {
            $trip = $this->currentCustomer()->transportBookings()->find($request->id);

            if (! $trip) {
                return $this->responseWithError(___('alert.not_found'));
            }
            if ($trip->status === 'Paid') {
                return $this->responseWithError('This trip is already paid.');
            }
            if ($trip->status === 'Cancelled') {
                return $this->responseWithError('A cancelled trip cannot be paid.');
            }
            if ((float) $trip->fare <= 0) {
                return $this->responseWithError('This trip has not been priced yet.');
            }

            if ($error = $this->walletShortfall($request->method, (float) $trip->fare)) {
                return $error;
            }

            app(\App\Actions\RecordPaymentClaim::class)(
                $trip,
                $trip->booking_no ?: 'Transport booking #' . $trip->id,
                (string) ($trip->customer_name ?: $this->currentCustomer()->name),
                (float) $trip->fare,
                (string) $request->method
            );

            return $this->responseWithSuccess(
                'Payment submitted — we will confirm it shortly and update your booking.'
            );
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /** Error response when paying by wallet without the balance, null otherwise. */
    /**
     * A document that belongs to the signed-in customer, or null.
     *
     * The portal must never hand over someone else's invoice because an id was
     * guessed in the URL, so the scope is applied here rather than trusted to
     * each caller.
     *
     * @param  string  $kind  invoice|receipt|hotel
     */
    public function ownedDocument(string $kind, $id)
    {
        $customerId = $this->currentCustomer()->id;

        return match ($kind) {
            'invoice' => \App\Models\Invoice::where('customer_id', $customerId)->find($id),
            'receipt' => \App\Models\Receipt::where('customer_id', $customerId)->find($id),
            'hotel'   => \App\Models\HotelBooking::where('customer_id', $customerId)->find($id),
            default   => null,
        };
    }

    /**
     * Begin an online checkout for a Pay Now click, when the chosen method is a
     * gateway the agency has switched on.
     *
     * Returns null for every offline method, which is what lets the existing
     * pay* methods below stay exactly as they were: they only ever see the
     * cash / bank / wallet cases now.
     *
     * @param  string  $kind  booking|hotel|transport
     * @return array|null  ['status' => true, 'redirect' => url] on success,
     *                     ['status' => false, 'message' => why] on failure
     */
    public function beginOnlinePayment($request, string $kind): ?array
    {
        $gateway = app(PaymentManager::class)->forMethod($request->method);

        if (! $gateway) {
            return null;
        }

        try {
            $customer = $this->currentCustomer();

            $payable = match ($kind) {
                'booking'   => $customer->bookings()->find($request->id),
                'hotel'     => $customer->hotelBookings()->find($request->id),
                'transport' => $customer->transportBookings()->find($request->id),
                default     => null,
            };

            if (! $payable) {
                return $this->responseWithError(___('alert.not_found'));
            }

            $service = app(PaymentIntentService::class);

            if ($reason = $service->unpayableReason($payable)) {
                return $this->responseWithError($reason);
            }

            $intent = $service->createFor($payable, $gateway, (int) $customer->id, 'portal');
            $charge = $service->start($intent, $gateway);

            return ['status' => true, 'redirect' => $charge['url'], 'message' => ''];
        } catch (\Throwable $th) {
            // The provider's own words are the useful part here — "store is not
            // active", "invalid credentials" — so they are shown, not swallowed.
            return $this->responseWithError($th->getMessage() ?: ___('alert.something_went_wrong'));
        }
    }

    /**
     * File the customer's "I have paid" against the booking for the desk.
     *
     * Deliberately does not touch `status`: that stays the agency's call, and
     * flipping it is what issues the receipt and the agent's commission.
     */
    private function recordPaymentClaim(\App\Models\Booking $booking, string $method): void
    {
        // Shared with the app's pay endpoints, so a declared payment is handled
        // the same way whichever surface the customer used.
        app(\App\Actions\RecordPaymentClaim::class)(
            $booking,
            'BKG-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
            (string) $booking->customer_name,
            (float) $booking->amount,
            $method
        );
    }

    private function walletShortfall(?string $method, float $amount): ?array
    {
        if ($method !== 'Wallet') {
            return null;
        }

        $balance = app(CustomerWalletService::class)->balance($this->currentCustomer()->id);

        return $balance + 0.009 < $amount
            ? $this->responseWithError('Insufficient wallet balance (' . currency_symbol() . number_format($balance, 2) . '). Please top up or pick another method.')
            : null;
    }

    public function visa()
    {
        return [
            // Documents eager-loaded so the portal can list what a customer
            // uploaded/was asked for, without a query per application.
            'applications' => $this->currentCustomer()->visaApplications()->with('documents')->latest()->get(),
        ];
    }

    /**
     * A visa document, only when it belongs to an application owned by the
     * signed-in customer — the same ownership-scoped lookup ownedDocument()
     * above uses for invoices/receipts. Returns null rather than 404ing here
     * so the controller can decide how to respond.
     */
    public function ownedVisaDocument($id): ?\App\Models\VisaDocument
    {
        return \App\Models\VisaDocument::whereHas(
            'visaApplication',
            fn ($q) => $q->where('customer_id', $this->currentCustomer()->id)
        )->find($id);
    }

    public function flights()
    {
        return [
            'flights' => $this->currentCustomer()->flightBookings()->latest()->get(),
        ];
    }

    /** Hotel stays booked by this customer, plus what the booking form needs. */
    public function hotels()
    {
        return [
            'bookings' => $this->currentCustomer()->hotelBookings()
                ->with(['hotel', 'hotelRoom'])->latest()->get(),
            'hotels'   => \App\Models\Hotel::orderBy('name')->get(['id', 'name', 'city']),
            'rooms'    => HotelRoom::get(['id', 'room_type', 'hotel_id', 'rate_per_night']),
            'methods'  => self::PAY_METHODS,
        ];
    }

    /** Transport trips requested or booked by this customer. */
    public function transport()
    {
        return [
            'trips'   => $this->currentCustomer()->transportBookings()->with('driver')->latest()->get(),
            'types'   => \App\Repositories\Transport\TransportRepository::TYPES,
            'methods' => self::PAY_METHODS,
        ];
    }

    /**
     * Invoices the agency issued to this customer. Note this reads `invoices`,
     * not `account_transactions` — the latter is the agency's own ledger and
     * has no customer column, so it must never be shown in the portal.
     */
    public function invoices()
    {
        return [
            'invoices' => $this->currentCustomer()->invoices()->latest('issue_date')->get(),
        ];
    }

    /** Receipts (payments) recorded against this customer's invoices. */
    public function payments()
    {
        return [
            'payments' => $this->currentCustomer()->receipts()->latest('received_on')->get(),
        ];
    }

    public function wallet()
    {
        $customer = $this->currentCustomer();

        $credit = (float) $customer->walletTransactions()->where('type', 'credit')->sum('amount');
        $debit  = (float) $customer->walletTransactions()->where('type', 'debit')->sum('amount');

        return [
            'transactions' => $customer->walletTransactions()->latest('txn_date')->get(),
            'balance'      => $this->walletBalance($customer),
            'earned'       => $credit,
            'spent'        => $debit,
        ];
    }

    /**
     * Running wallet balance = balance_after on the customer's newest
     * transaction — ledger truth, matching the mobile API (DashboardController
     * / WalletController). Do not derive from credit/debit sums: those drift
     * from the ledger whenever an entry carries an adjustment or a seeded
     * opening balance.
     */
    protected function walletBalance(Customer $customer): float
    {
        $latest = $customer->walletTransactions()->orderByDesc('id')->first();

        return $latest ? (float) $latest->balance_after : 0.0;
    }

    public function documents()
    {
        return [
            'documents' => $this->currentCustomer()->documents()->latest('uploaded_on')->get(),
        ];
    }

    public function support()
    {
        return [
            'tickets'     => $this->currentCustomer()->supportTickets()->latest()->get(),
            'priorities'  => self::TICKET_PRIORITIES,
            'departments' => self::TICKET_DEPARTMENTS,
        ];
    }

    /** Open a new support ticket on behalf of the logged-in customer. */
    public function storeTicket($request)
    {
        try {
            $customer = $this->currentCustomer();

            $customer->supportTickets()->create([
                'ticket_no'     => 'TKT-' . str_pad((string) (SupportTicket::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'subject'       => $request->subject,
                'customer_name' => $customer->name,
                'priority'      => $request->priority,
                'department'    => $request->department ?: null,
                'status'        => 'Open',
            ]);

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function settings()
    {
        return ['user' => auth()->user()];
    }

    /** Save the portal user's own name + preferences from the Settings screen. */
    public function updateSettings($request)
    {
        try {
            $user = auth()->user();
            $user->name        = $request->name;
            $user->preferences = [
                'language'            => $request->language,
                'currency'            => $request->currency,
                'email_notifications' => (bool) $request->boolean('email_notifications'),
                'sms_alerts'          => (bool) $request->boolean('sms_alerts'),
            ];
            $user->save();

            if ($customer = $user->customer) {
                $customer->name = $user->name;
                $customer->save();
            }

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    /* ---------------------------------------------------------------------
     | Wishlist — saved tour packages (the heart icon on the public site)
     * ------------------------------------------------------------------- */

    public function wishlist()
    {
        return [
            'items' => $this->currentCustomer()->wishlists()->with('package')->latest()->get(),
        ];
    }

    /**
     * Toggle a package on/off the signed-in customer's wishlist. Shared by the
     * public heart icon and (indirectly, via the same customer identity) the
     * portal's My Wishlist page, so both surfaces always agree.
     */
    public function toggleWishlist($packageId): array
    {
        $package = Package::where('status', 'active')->find($packageId);

        if (! $package) {
            return $this->responseWithError('This package is not available.', [], 404);
        }

        $customer = $this->currentCustomer();
        $existing = Wishlist::where('customer_id', $customer->id)->where('package_id', $package->id)->first();

        if ($existing) {
            $existing->delete();

            return $this->responseWithSuccess('Removed from your wishlist.', ['wishlisted' => false]);
        }

        Wishlist::create(['customer_id' => $customer->id, 'package_id' => $package->id]);

        return $this->responseWithSuccess('Saved to your wishlist.', ['wishlisted' => true]);
    }

    public function removeWishlist($id)
    {
        try {
            $this->currentCustomer()->wishlists()->findOrFail($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function wishlistedPackageIds(): array
    {
        if (! auth()->check()) {
            return [];
        }

        $customer = Customer::where('email', auth()->user()->email)->first();

        return $customer ? $customer->wishlists()->pluck('package_id')->all() : [];
    }
}
