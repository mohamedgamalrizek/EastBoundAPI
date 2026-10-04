<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\VisaApplication;
use App\Models\VisaService;
use Illuminate\Support\Str;

/**
 * Opens a visa case for a tour booking whose customer said they need one.
 *
 * Visa sits outside package pricing, so without this the requirement is only
 * discovered when somebody happens to ask — often days before departure. Both
 * the website form and the mobile app route through here so a case looks the
 * same whichever channel created it.
 */
class OpenVisaCaseForBooking
{
    public function __invoke(Booking $booking, Package $package, Customer $customer): ?VisaApplication
    {
        // One case per booking; re-submitting the form must not duplicate it.
        $existing = VisaApplication::where('customer_id', $customer->id)
            ->where('package_id', $package->id)
            ->whereIn('status', ['Processing', 'In Review'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $country = $this->destinationCountry($package);
        $service = $this->catalogueEntryFor($country);

        return VisaApplication::create([
            'customer_id'      => $customer->id,
            'package_id'       => $package->id,
            'visa_service_id'  => $service?->id,
            'application_no'   => $this->reference($booking),
            'applicant_name'   => $booking->customer_name ?: $customer->name,
            'country'          => $service->country ?? $country,
            'visa_type'        => $service->visa_type ?? 'Tourist',
            // Quoted from the catalogue when the destination is published, so
            // staff see the price without looking it up.
            'govt_fee'         => $service?->govt_fee,
            'service_fee'      => $service?->service_fee,
            'applied_date'     => null,
            'documents_status' => 'Pending',
            'status'           => 'Processing',
        ]);
    }

    /** Destinations are stored as "City, Country". */
    private function destinationCountry(Package $package): string
    {
        $tail = trim((string) Str::afterLast($package->destination, ','));

        return $tail !== '' ? $tail : (string) $package->destination;
    }

    private function catalogueEntryFor(string $country): ?VisaService
    {
        return VisaService::active()
            ->where('country', $country)
            ->orderByDesc('is_featured')
            ->first();
    }

    private function reference(Booking $booking): string
    {
        return 'VISA-' . str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT)
            . '-' . now()->format('my');
    }
}
