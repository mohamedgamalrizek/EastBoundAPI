<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Stripe — Checkout Sessions (hosted checkout).
 *
 * Docs: https://docs.stripe.com/api/checkout/sessions
 * Base: https://api.stripe.com/v1, bearer auth with the secret key, and the
 * request body is application/x-www-form-urlencoded with bracketed nested keys.
 */
class StripeGateway extends AbstractGateway
{
    private const BASE = 'https://api.stripe.com/v1';

    public function key(): string
    {
        return 'stripe';
    }

    public function label(): string
    {
        return 'Stripe';
    }

    public function region(): string
    {
        return 'International';
    }

    public function currencies(): array
    {
        return ['USD', 'EUR', 'GBP'];
    }

    protected function requiredKeys(): array
    {
        return ['secret'];
    }

    public function initiate(Payment $payment): array
    {
        $secret   = $this->config('secret');
        $callback = $this->callbackUrl($payment);

        // callbackUrl already carries ?gateway=...&reference=..., so append with &.
        $successUrl = $callback . '&session_id={CHECKOUT_SESSION_ID}';
        $currency   = strtolower($payment->currency ?: 'usd');

        $response = $this->http()
            ->withToken($secret)
            ->asForm()
            ->post(self::BASE . '/checkout/sessions', [
                'mode'        => 'payment',
                'success_url' => $successUrl,
                'cancel_url'  => $callback,
                'line_items[0][price_data][currency]'                  => $currency,
                'line_items[0][price_data][product_data][name]'        => 'Subscription',
                'line_items[0][price_data][unit_amount]'               => (int) round(((float) $payment->amount) * 100),
                'line_items[0][quantity]'                              => 1,
                'client_reference_id'                                  => $payment->reference,
            ]);

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? 'Unable to create Stripe Checkout Session.';
            $this->fail($error);
        }

        $url       = $response->json('url');
        $sessionId = $response->json('id');

        if (blank($url) || blank($sessionId)) {
            $this->fail('Stripe did not return a checkout URL.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'session_id' => $sessionId,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $url];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $secret = $this->config('secret');

        $sessionId = $request->query('session_id')
            ?: ((array) $payment->payload)['session_id'] ?? null;

        if (blank($sessionId)) {
            return false;
        }

        $response = $this->http()
            ->withToken($secret)
            ->get(self::BASE . '/checkout/sessions/' . $sessionId);

        if (! $response->successful()) {
            return false;
        }

        if (($response->json('payment_status') ?? null) !== 'paid') {
            return false;
        }

        $payment->update([
            'gateway_ref' => $response->json('payment_intent') ?? $sessionId,
        ]);

        return true;
    }
}
