<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Flutterwave (v3 hosted checkout) — package-free.
 *
 * Auth is a bearer secret key. initiate() creates a Standard payment and
 * redirects to the hosted link; verify() confirms the transaction via the
 * official verify endpoint on the callback.
 */
class FlutterwaveGateway extends AbstractGateway
{
    private const BASE = 'https://api.flutterwave.com/v3';

    public function key(): string
    {
        return 'flutterwave';
    }

    public function label(): string
    {
        return 'Flutterwave';
    }

    public function region(): string
    {
        return 'International';
    }

    public function currencies(): array
    {
        return ['NGN', 'USD', 'GHS', 'KES'];
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
            ->post(self::BASE . '/payments', [
                'tx_ref'       => $payment->reference,
                'amount'       => (string) $payment->amount,
                'currency'     => $payment->currency ?: 'USD',
                'redirect_url' => $this->callbackUrl($payment),
                'customer'     => [
                    'email' => $payment->payer_email,
                    'name'  => $payment->payer_name,
                ],
                'customizations' => [
                    'title' => 'Subscription',
                ],
            ]);

        if (! $response->successful()) {
            $this->fail('Payment initiation failed: ' . $response->body());
        }

        $body = $response->json();
        $status = $body['status'] ?? null;
        $link = $body['data']['link'] ?? null;

        if ($status !== 'success' || blank($link)) {
            $this->fail($body['message'] ?? 'Unable to create payment link.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'flw_link' => $link,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $link];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $status = $request->query('status');
        $transactionId = $request->query('transaction_id');

        if ($status !== 'successful' && $status !== 'completed') {
            return false;
        }

        if (blank($transactionId)) {
            return false;
        }

        $secret = $this->config('secret_key');

        $response = $this->http()
            ->withToken($secret)
            ->get(self::BASE . '/transactions/' . rawurlencode((string) $transactionId) . '/verify');

        if (! $response->successful()) {
            return false;
        }

        $body = $response->json();
        $data = $body['data'] ?? [];

        $verifiedStatus = $data['status'] ?? null;
        $verifiedTxRef = $data['tx_ref'] ?? null;

        if ($verifiedStatus === 'successful' && $verifiedTxRef === $payment->reference) {
            $payment->update(['gateway_ref' => (string) $transactionId]);
            return true;
        }

        return false;
    }
}
