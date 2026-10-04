<?php

namespace App\Actions;

use App\Models\Notification;
use App\Models\User;

/**
 * The customer says they have paid; the agency confirms it.
 *
 * Marking a booking paid runs the billing observers: a receipt is issued, the
 * ledger is posted and the selling agent's commission is created. Doing that
 * on the customer's word alone puts money in the books that nobody received —
 * and, on an agent sale, creates a commission the agency now owes.
 *
 * So a declaration is recorded against the booking and the desk is told. The
 * desk marks it paid once the money is actually in, which is the point where
 * every one of those consequences is supposed to happen.
 *
 * Not used for gateway payments: there the provider collects the money and its
 * callback settles the booking, so there is nothing to take on trust.
 */
class RecordPaymentClaim
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $record    Booking, HotelBooking or TransportBooking.
     * @param  string  $reference  What the customer would call it, e.g. BKG-00042.
     */
    public function __invoke($record, string $reference, string $customerName, float $amount, string $method): void
    {
        $note = sprintf(
            '[%s] Customer submitted payment of %s%s via %s — awaiting confirmation.',
            now()->format('d M Y H:i'),
            currency_symbol(),
            number_format($amount, 2),
            $method
        );

        $payload = ['payment_method' => $method, 'payment_claimed_at' => now()];

        // Only tour bookings carry a notes column; the other two keep the
        // claim in the desk notification alone.
        if (in_array('notes', $record->getFillable(), true)) {
            $payload['notes'] = trim(($record->notes ?? '') . "\n" . $note);
        }

        $record->update($payload);

        $this->notifyDesk($reference, $customerName, $amount, $method);
    }

    /**
     * Tell everyone who can confirm a payment that one is waiting. Sent to the
     * whole desk rather than one person, so the money is not left unconfirmed
     * because a single inbox went unread.
     */
    private function notifyDesk(string $reference, string $customer, float $amount, string $method): void
    {
        User::whereHas('role', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin', 'accountant']))
            ->get()
            ->each(fn ($staff) => Notification::notify(
                $staff,
                'Payment awaiting confirmation',
                "{$reference} — {$customer} says they paid "
                    . currency_symbol() . number_format($amount, 2) . " via {$method}.",
                'booking'
            ));
    }
}
