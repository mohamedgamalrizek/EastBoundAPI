<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Paystack hosted checkout (package-free).
 *
 * Docs flow:
 *   initiate → POST /transaction/initialize (Bearer secret_key)
 *              returns data.authorization_url → redirect the payer there.
 *   verify   → GET  /transaction/verify/{reference} (Bearer secret_key)
 *              data.status === 'success' confirms the payment.
 */
class PaystackGateway extends AbstractGateway
{
    private const BASE = 'https://api.paystack.co';

    public function key(): string
    {
        return 'paystack';
    }

    public function label(): string
    {
        return 'Paystack';
    }

    public function region(): string
    {
        return 'International';
    }

    public function currencies(): array
    {
        return ['NGN', 'GHS', 'USD', 'ZAR'];
    }

    protected function requiredKeys(): array
    {
        return ['secret_key'];
    }

    public function initiate(Payment $payment): array
    {
        $secret = $this->config('secret_key');

        $response = $this->http()
            ->withToken($secret)
            ->post(self::BASE . '/transaction/initialize', [
                'email'        => $payment->payer_email,
                'amount'       => (int) round(((float) $payment->amount) * 100),
                'currency'     => $payment->currency ?: 'NGN',
                'reference'    => $payment->reference,
                'callback_url' => $this->callbackUrl($payment),
            ]);

        if (! $response->successful()) {
            $this->fail('initialize failed: ' . $response->body());
        }

        $body = $response->json();

        if (($body['status'] ?? false) !== true) {
            $this->fail('initialize rejected: ' . ($body['message'] ?? 'unknown error'));
        }

        $data    = $body['data'] ?? [];
        $authUrl = $data['authorization_url'] ?? null;

        if (blank($authUrl)) {
            $this->fail('no authorization_url returned');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'paystack_access_code' => $data['access_code'] ?? null,
                'paystack_reference'   => $data['reference'] ?? $payment->reference,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $authUrl];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $secret    = $this->config('secret_key');
        $reference = $request->query('reference', $payment->reference);

        if (blank($reference)) {
            return false;
        }

        $response = $this->http()
            ->withToken($secret)
            ->get(self::BASE . '/transaction/verify/' . rawurlencode($reference));

        if (! $response->successful()) {
            return false;
        }

        $body = $response->json();

        if (($body['status'] ?? false) !== true) {
            return false;
        }

        $data = $body['data'] ?? [];

        if (($data['status'] ?? null) !== 'success') {
            return false;
        }

        $txnId = $data['id'] ?? $data['reference'] ?? $reference;

        $payment->update(['gateway_ref' => (string) $txnId]);

        return true;
    }
}
