<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Cashfree Payment Gateway — Orders API v3 (package-free).
 *
 * Docs flow:
 *   initiate → POST {base}/orders → response has 'payment_session_id'.
 *              Redirect the payer to Cashfree's hosted checkout view.
 *   verify   → GET  {base}/orders/{order_id} → 'order_status' === 'PAID'.
 */
class CashfreeGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'cashfree';
    }

    public function label(): string
    {
        return 'Cashfree';
    }

    public function region(): string
    {
        return 'India';
    }

    public function currencies(): array
    {
        return ['INR'];
    }

    protected function requiredKeys(): array
    {
        return ['app_id', 'secret_key'];
    }

    /** Cashfree PG REST base URL for the current environment. */
    protected function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.cashfree.com/pg'
            : 'https://api.cashfree.com/pg';
    }

    /** Hosted checkout host for the current environment. */
    protected function checkoutHost(): string
    {
        return $this->sandbox()
            ? 'https://payments-test.cashfree.com'
            : 'https://payments.cashfree.com';
    }

    /** Auth + version headers required on every PG call. */
    protected function authHeaders(): array
    {
        return [
            'x-client-id'     => (string) $this->config('app_id'),
            'x-client-secret' => (string) $this->config('secret_key'),
            'x-api-version'   => '2023-08-01',
        ];
    }

    public function initiate(Payment $payment): array
    {
        $returnUrl = $this->callbackUrl($payment)
            . (str_contains($this->callbackUrl($payment), '?') ? '&' : '?')
            . 'reference=' . urlencode($payment->reference);

        $response = $this->http()
            ->withHeaders($this->authHeaders())
            ->post($this->baseUrl() . '/orders', [
                'order_id'       => $payment->reference,
                'order_amount'   => (float) $payment->amount,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id'    => 'cust_' . $payment->id,
                    'customer_name'  => $payment->payer_name ?? 'Customer',
                    'customer_email' => $payment->payer_email ?? 'customer@example.com',
                    'customer_phone' => '9999999999',
                ],
                'order_meta' => [
                    'return_url' => $returnUrl,
                ],
            ]);

        if (! $response->successful()) {
            $this->fail('Order creation failed: ' . ($response->json('message') ?? $response->body()));
        }

        $sessionId = $response->json('payment_session_id');
        $cfOrderId = $response->json('cf_order_id');

        if (blank($sessionId)) {
            $this->fail('Missing payment_session_id in order response.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'payment_session_id' => $sessionId,
                'cf_order_id'        => $cfOrderId,
            ]),
        ]);

        return [
            'type' => 'redirect',
            'url'  => $this->checkoutHost() . '/pg/view/checkout?payment_session_id=' . urlencode($sessionId),
        ];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $response = $this->http()
            ->withHeaders($this->authHeaders())
            ->get($this->baseUrl() . '/orders/' . $payment->reference);

        if (! $response->successful()) {
            return false;
        }

        $status = $response->json('order_status');

        if ($status !== 'PAID') {
            return false;
        }

        $txnId = $response->json('cf_order_id') ?? $response->json('order_id');

        $payment->update(['gateway_ref' => $txnId]);

        return true;
    }
}
