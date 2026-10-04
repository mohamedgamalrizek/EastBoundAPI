<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * PayPal — Orders v2 (package-free, official REST API).
 *
 * Flow:
 *   initiate → OAuth2 client_credentials token → create CAPTURE order →
 *              redirect payer to the "approve" link.
 *   verify   → on callback (?token=<orderId>) re-auth, capture the order,
 *              confirm status COMPLETED and store the capture id.
 */
class PaypalGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'paypal';
    }

    public function label(): string
    {
        return 'PayPal';
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
        return ['client_id', 'client_secret'];
    }

    /** Sandbox vs live API base host. */
    protected function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /** Fetch an OAuth2 access token via client_credentials. */
    protected function accessToken(): string
    {
        $response = $this->http()
            ->asForm()
            ->withBasicAuth(
                (string) $this->config('client_id'),
                (string) $this->config('client_secret')
            )
            ->post($this->baseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            $this->fail('Unable to obtain access token.');
        }

        $token = $response->json('access_token');

        if (blank($token)) {
            $this->fail('Access token missing in response.');
        }

        return (string) $token;
    }

    public function initiate(Payment $payment): array
    {
        $token = $this->accessToken();

        $callback = $this->callbackUrl($payment);

        $response = $this->http()
            ->withToken($token)
            ->post($this->baseUrl() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $payment->reference,
                    'amount' => [
                        'currency_code' => $payment->currency ?: 'USD',
                        'value' => number_format((float) $payment->amount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => $callback,
                    'cancel_url' => $callback,
                ],
            ]);

        if (! $response->successful()) {
            $this->fail('Unable to create order.');
        }

        $orderId = $response->json('id');

        if (blank($orderId)) {
            $this->fail('Order id missing in response.');
        }

        // Locate the "approve" link the payer must be sent to.
        $approveUrl = null;
        foreach ((array) $response->json('links', []) as $link) {
            if (($link['rel'] ?? null) === 'approve' && ! blank($link['href'] ?? null)) {
                $approveUrl = $link['href'];
                break;
            }
        }

        if (blank($approveUrl)) {
            $this->fail('Approve URL missing in response.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'order_id' => $orderId,
            ]),
        ]);

        return [
            'type' => 'redirect',
            'url'  => $approveUrl,
        ];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        // PayPal returns ?token=<orderId> on the approval redirect.
        $orderId = $request->query('token');

        if (blank($orderId)) {
            $orderId = data_get((array) $payment->payload, 'order_id');
        }

        if (blank($orderId)) {
            return false;
        }

        $token = $this->accessToken();

        $response = $this->http()
            ->withToken($token)
            ->post($this->baseUrl() . '/v2/checkout/orders/' . $orderId . '/capture');

        if (! $response->successful()) {
            return false;
        }

        if ($response->json('status') !== 'COMPLETED') {
            return false;
        }

        $captureId = $response->json('purchase_units.0.payments.captures.0.id')
            ?? $response->json('id');

        if (! blank($captureId)) {
            $payment->update(['gateway_ref' => $captureId]);
        }

        return true;
    }
}
