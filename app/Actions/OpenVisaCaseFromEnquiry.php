<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\VisaApplication;
use App\Models\VisaService;

/**
 * Turns a self-service "Visa Application" — the website form or the mobile
 * app — into a real visa case.
 *
 * The website form used to create only a CRM lead, so an applicant who filled
 * it in appeared nowhere in the Visa module: not in Applications, not in
 * Status Tracking, and "Track Your Visa" could never find them. The app's
 * endpoint did create a case but stamped `status: pending` and
 * `documents_status: Collecting`, neither of which is in the module's
 * vocabulary — so those cases fell outside every tile and the desk could not
 * advance them at all, because the transition table has no rule for a status
 * that does not exist.
 *
 * One shape for both: a Processing case with Pending documents, priced from
 * the published catalogue when the destination is one we sell.
 *
 * @see OpenVisaCaseForBooking for the case raised alongside a tour booking.
 */
class OpenVisaCaseFromEnquiry
{
    /**
     * @param  array  $data  name, phone, email, details, country, visa_type,
     *                       nationality, travel_date
     */
    public function __invoke(Customer $customer, array $data): VisaApplication
    {
        $country = trim((string) ($data['country'] ?? ''));
        $type    = trim((string) ($data['visa_type'] ?? '')) ?: 'Tourist';

        // Re-submitting the form (or a double click) must not open a second
        // case for the same destination while the first is still being worked.
        $existing = VisaApplication::where('customer_id', $customer->id)
            ->where('country', $country)
            ->whereIn('status', ['Processing', 'In Review'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $service = $this->catalogueEntryFor($country, $type);

        return VisaApplication::create([
            'customer_id'      => $customer->id,
            'visa_service_id'  => $service?->id,
            'application_no'   => $this->nextApplicationNo(),
            'applicant_name'   => $data['name'] ?? $customer->name,
            'country'          => $service->country ?? $country,
            'visa_type'        => $service->visa_type ?? $type,
            // Quoted from the catalogue when the destination is published, so
            // staff see the price without looking it up.
            'govt_fee'         => $service?->govt_fee,
            'service_fee'      => $service?->service_fee,
            // Not filed with an embassy yet: advance() stamps this the first
            // time the case reaches In Review. Setting it here would make the
            // applicant's tracking page claim the file was already lodged.
            'applied_date'     => null,
            'documents_status' => 'Pending',
            'status'           => 'Processing',
            // Nationality, travel date and free text have no columns of their
            // own; they are what the consultant needs on first contact, so
            // they stay with the case rather than being dropped.
            'appointment_notes' => $this->intake($data),
        ]);
    }

    /** Prefer the exact country + type entry, else any entry for the country. */
    private function catalogueEntryFor(string $country, string $type): ?VisaService
    {
        if ($country === '') {
            return null;
        }

        return VisaService::active()
            ->where('country', $country)
            ->where('visa_type', $type)
            ->first()
            ?? VisaService::active()
                ->where('country', $country)
                ->orderByDesc('is_featured')
                ->first();
    }

    /** Next number in the seeded VISA-#### series. */
    private function nextApplicationNo(): string
    {
        $last = VisaApplication::where('application_no', 'like', 'VISA-%')
            ->selectRaw('MAX(CAST(SUBSTRING(application_no, 6) AS UNSIGNED)) AS n')
            ->value('n');

        return 'VISA-' . max(3301, ((int) $last) + 1);
    }

    /** What the applicant told us on the form, kept in one readable block. */
    private function intake(array $data): string
    {
        return collect([
            'Nationality' => $data['nationality'] ?? null,
            'Travel date' => $data['travel_date'] ?? null,
            'Phone'       => $data['phone'] ?? null,
            'Email'       => $data['email'] ?? null,
            'Notes'       => $data['details'] ?? null,
        ])
            ->filter()
            ->map(fn ($value, $label) => "{$label}: {$value}")
            ->prepend('Submitted from the website visa form.')
            ->implode("\n");
    }
}
