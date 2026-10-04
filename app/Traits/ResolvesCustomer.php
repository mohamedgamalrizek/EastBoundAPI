<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Resolves the authenticated customer for a request.
 *
 * App users are `customers` records; web portal / staff-role users are
 * `users` records. Users holding the "Customer" role are linked to (or
 * seeded as) a `customers` record via email so every surface — app, web
 * portal, API — reports through the same customer identity.
 */
trait ResolvesCustomer
{
    private function customer(Request $request): ?Customer
    {
        $user = $request->user();

        if ($user instanceof Customer) {
            return $user;
        }

        if (! $user instanceof User) {
            return null;
        }

        $role = strtolower((string) optional($user->role)->name);
        if (! str_contains($role, 'customer')) {
            return null;
        }

        return Customer::firstOrCreate(
            ['email' => $user->email],
            [
                'name'   => $user->name ?? 'Customer',
                'phone'  => $user->phone,
                'status' => 'active',
            ]
        );
    }

    /**
     * The customer behind a booking taken on somebody else's behalf — a public
     * website form, or an agent booking for their client. An existing match on
     * email/phone is reused, otherwise a fresh record is opened so the booking
     * is never left orphaned: a booking without customer_id is invisible on the
     * customer's profile and in their portal's "My Bookings".
     *
     * Keys: customer_name, customer_email, customer_phone.
     */
    private function resolveCustomerFromContact(array $data, string $notes): Customer
    {
        $email = $data['customer_email'] ?? null;
        $phone = $data['customer_phone'] ?? null;

        // With neither identifier there is nothing to match on, and an
        // unconstrained lookup would return whichever customer happens to be
        // first — so open a fresh record instead.
        if (filled($email) || filled($phone)) {
            $existing = Customer::where(function ($q) use ($email, $phone) {
                if (filled($email)) {
                    $q->orWhere('email', $email);
                }
                if (filled($phone)) {
                    $q->orWhere('phone', $phone);
                }
            })->first();

            if ($existing) {
                return $existing;
            }
        }

        return Customer::create([
            'name'   => $data['customer_name'],
            'email'  => $email,
            'phone'  => $phone,
            'status' => 'active',
            'notes'  => $notes,
        ]);
    }
}
