<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Package;

/**
 * Turns a self-service tour enquiry — the website's Tour / Custom Tour form —
 * into a real Booking (pending), the same record the package page's "Book
 * Now" button already raises via FrontendController::bookStore().
 *
 * A generic enquiry rarely names an exact package the way "Book Now" from a
 * package page does, so package_id stays null when the applicant picked "Not
 * sure, advise me" — or the form never offered a catalogue at all, as on
 * Custom Tour. The desk still gets a real, trackable pending booking rather
 * than a lead with the destination buried in prose, and can attach the right
 * package once someone has actually spoken to the applicant.
 */
class OpenTourBookingFromEnquiry
{
    /**
     * @param  array  $data  name, phone, email, from, to, package (title
     *                       text, optional), departure_date, return_date,
     *                       travelers, details
     */
    public function __invoke(Customer $customer, array $data): Booking
    {
        $package = filled($data['package'] ?? null)
            ? Package::where('status', 'active')->where('title', $data['package'])->first()
            : null;

        $travelers = (int) ($data['travelers'] ?? 1) ?: 1;

        // Re-submitting the form (or a double click) must not open a second
        // booking for the same trip while the first is still pending.
        $existing = Booking::where('customer_id', $customer->id)
            ->where('package_id', $package?->id)
            ->where('travel_date', $data['departure_date'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'package_id'     => $package?->id,
            'customer_name'  => $data['name'] ?? $customer->name,
            'customer_email' => $data['email'] ?? $customer->email,
            'customer_phone' => $data['phone'] ?? $customer->phone,
            'travel_date'    => $data['departure_date'],
            'travelers'      => $travelers,
            // Quoted from the catalogue when a real package was matched;
            // otherwise the desk prices it once they know what the applicant
            // actually wants — the same "priced later" state a transport
            // request is left in.
            'amount'         => $package ? $package->price * $travelers : 0,
            'status'         => 'pending',
            'notes'          => 'Booking requested from website.'
                . (! $package && filled($data['to'] ?? null) ? ' Destination: ' . $data['to'] : ''),
        ]);

        $this->lead($booking, $customer, $package, $data);

        Notification::notify($customer, 'Tour enquiry received',
            "We've received your tour request" . ($package ? " for {$package->title}" : '') . '. We\'ll confirm shortly.', 'booking');

        return $booking;
    }

    /**
     * Answers with no column of their own (origin, a return date, the
     * destination when it didn't match a catalogue package) stay with the
     * consultant as a lead.
     */
    private function lead(Booking $booking, Customer $customer, ?Package $package, array $data): void
    {
        $fields = ['from', 'return_date'];
        if (! $package) {
            $fields[] = 'to';
        }

        $extra = collect($data)->only($fields)
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . $v)
            ->implode("\n");

        Lead::create([
            'name'     => $data['name'] ?? $customer->name,
            'phone'    => $data['phone'] ?? $customer->phone,
            'email'    => $data['email'] ?? $customer->email,
            'interest' => $package ? $package->title : 'Tour Enquiry',
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim("[Website] Tour booking request (Booking #{$booking->id}).\n"
                . $extra . "\n" . ($data['details'] ?? '')),
        ]);
    }
}
