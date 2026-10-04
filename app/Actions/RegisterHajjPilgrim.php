<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\HajjPackage;
use App\Models\HajjPilgrim;
use App\Models\Lead;
use App\Models\Notification;

/**
 * Register somebody on a Hajj / Umrah package.
 *
 * One path for every channel — the public website form, the mobile app, and
 * anything added later. It used to exist only inside the app's API controller,
 * so a registration from the website produced a CRM lead and nothing else: the
 * Hajj module never heard about it, and the "data" was a block of prose in a
 * notes field rather than a pilgrim record.
 *
 * What a registration is:
 *   - a HajjPilgrim, status Registered, owing the package price (the billing
 *     observer raises INV-P… from that);
 *   - one seat off the package;
 *   - a CRM lead carrying the preferences that have no column of their own
 *     (room sharing, preferred month, how many people), so the consultant
 *     still has them.
 */
class RegisterHajjPilgrim
{
    /**
     * @param  array  $input  name, passport_no, group_name, phone, email,
     *                        package_pref, pilgrims, preferred_month,
     *                        room_sharing, details
     * @param  string $channel  Where it came from, for the lead note.
     *
     * @throws \DomainException when the package cannot take this registration.
     */
    public function __invoke(HajjPackage $package, Customer $customer, array $input, string $channel = 'Website'): HajjPilgrim
    {
        if ((int) $package->seats <= 0) {
            throw new \DomainException('No seats left in this package.');
        }

        $already = HajjPilgrim::where('customer_id', $customer->id)
            ->where('hajj_package_id', $package->id)
            ->whereRaw('LOWER(status) != ?', ['cancelled'])
            ->exists();

        if ($already) {
            throw new \DomainException('You are already registered for this package.');
        }

        $pilgrim = HajjPilgrim::create([
            'hajj_package_id' => $package->id,
            'customer_id'     => $customer->id,
            'pilgrim_no'      => 'PLG-' . strtoupper(substr(uniqid(), -6)),
            'name'            => $input['name'] ?? $customer->name,
            'passport_no'     => $input['passport_no'] ?? null,
            'package_title'   => $package->title,
            'group_name'      => $input['group_name'] ?? null,
            'payment_status'  => 'Pending',
            'document_status' => 'Pending',
            'status'          => 'Registered',
            // Anchor the money to the package price: instalments count down
            // from here, and the billing observer raises the matching invoice.
            'amount_due'      => (float) ($package->price ?? 0),
        ]);

        $package->decrement('seats');

        $this->lead($package, $customer, $pilgrim, $input, $channel);

        Notification::notify(
            $customer,
            "{$package->type} registration received",
            "You are registered for {$package->title} ({$pilgrim->pilgrim_no}). We'll contact you for documents.",
            'booking'
        );

        return $pilgrim;
    }

    /**
     * The preference answers have no column on hajj_pilgrims, so they are
     * filed as a lead the consultant works from — tagged with the channel so
     * the CRM can still tell a website registration from an app one.
     */
    private function lead(HajjPackage $package, Customer $customer, HajjPilgrim $pilgrim, array $input, string $channel): void
    {
        $extra = collect($input)
            ->only(['package_pref', 'pilgrims', 'preferred_month', 'room_sharing', 'group_name'])
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . $v)
            ->implode("\n");

        Lead::create([
            'name'     => $pilgrim->name,
            'phone'    => $input['phone'] ?? $customer->phone,
            'email'    => $input['email'] ?? $customer->email,
            'interest' => "{$package->type} Registration",
            // leads.source is an enum; everything self-service rides under
            // Website and is tagged in the notes.
            'source'   => 'Website',
            'stage'    => 'New',
            'notes'    => trim("[{$channel}] {$package->type} registration {$pilgrim->pilgrim_no}.\n"
                . "Package: {$package->title} ({$package->package_no})\n"
                . "Passport: {$pilgrim->passport_no}\n"
                . $extra . "\n" . ($input['details'] ?? '')),
        ]);
    }
}
