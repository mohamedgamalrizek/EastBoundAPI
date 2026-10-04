<?php

namespace App\Services\Payments\Contracts;

use App\Models\PaymentIntent;
use Illuminate\Http\Request;

/**
 * One contract every online gateway implements. The flow is uniform across
 * providers (hosted / redirect checkout):
 *
 *   1. PaymentIntentService creates a pending PaymentIntent
 *   2. initiate()  → calls the provider's official API and returns a charge
 *                    descriptor the controller turns into a browser redirect
 *   3. the payer pays on the provider's page
 *   4. the provider returns to our callback URL → verify() re-checks the
 *      transaction server-side and confirms it
 *
 * Step 4 never trusts the callback's own query string on its own: a redirect
 * back to our site is attacker-controllable, so each gateway re-asks the
 * provider whether the money actually arrived.
 *
 * No SDK packages — each gateway calls the provider's REST API directly.
 */
interface PaymentGateway
{
    /** Stable machine key, e.g. 'bkash'. Also the settings prefix. */
    public function key(): string;

    /** Human label, e.g. 'bKash'. */
    public function label(): string;

    /** The receipt method this gateway settles as — must be one of LedgerService::METHOD_ACCOUNTS. */
    public function receiptMethod(): string;

    /** Currencies this gateway accepts, e.g. ['BDT']. */
    public function currencies(): array;

    /** True when the agency has switched this gateway on in Settings. */
    public function isEnabled(): bool;

    /** True when every required credential is filled in. */
    public function isConfigured(): bool;

    /**
     * Start the payment with the provider. Return a charge descriptor:
     *   ['type' => 'redirect', 'url' => 'https://gateway/pay/abc']
     * Throw \RuntimeException on an API error.
     */
    public function initiate(PaymentIntent $intent): array;

    /**
     * Handle the provider's return/callback for $intent. Inspect $request and
     * call the provider's verify/execute API. Set $intent->gateway_ref and
     * return true only when the money is confirmed captured.
     */
    public function verify(Request $request, PaymentIntent $intent): bool;
}
