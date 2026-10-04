<?php

namespace Modules\Saas\Payments\Contracts;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;

/**
 * One contract every gateway implements. The flow is uniform across all
 * providers (hosted/redirect checkout):
 *
 *   1. controller creates a pending Payment
 *   2. initiate()  → calls the provider's official API, returns a "charge"
 *                    descriptor the controller turns into a browser redirect
 *   3. user pays on the provider's page
 *   4. provider returns to our callback URL → verify() confirms + captures
 *
 * No SDK packages — each gateway calls the provider's REST API directly.
 */
interface PaymentGateway
{
    /** Stable machine key, e.g. 'bkash'. Matches config('payment.fields.<key>'). */
    public function key(): string;

    /** Human label, e.g. 'bKash'. */
    public function label(): string;

    /** 'Bangladesh' | 'India' | 'International'. */
    public function region(): string;

    /** Currencies this gateway accepts, e.g. ['BDT']. */
    public function currencies(): array;

    /** True when all required credentials are present in config. */
    public function isConfigured(): bool;

    /**
     * Start the payment with the provider. Return a charge descriptor:
     *   ['type' => 'redirect', 'url' => 'https://gateway/pay/abc']
     *   ['type' => 'post',     'url' => 'https://gateway/pay', 'fields' => ['k' => 'v', ...]]
     * Throw \RuntimeException on an API error.
     */
    public function initiate(Payment $payment): array;

    /**
     * Handle the provider's return/callback for $payment. Inspect $request and,
     * where needed, call the provider's verify/query API. Set
     * $payment->gateway_ref and return true when the payment is confirmed paid.
     */
    public function verify(Request $request, Payment $payment): bool;
}
