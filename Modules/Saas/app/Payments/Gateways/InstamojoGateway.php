<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Instamojo (India) — REST API v1.1, package-free.
 *
 * Auth headers on every call: X-Api-Key + X-Auth-Token.
 * initiate(): create a payment-request, redirect the payer to its longurl.
 * verify():   the callback carries payment_id / payment_request_id; we re-query
 *             the payment-request and confirm a payment with status 'Credit'.
 */
class InstamojoGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'instamojo';
    }

    public function label(): string
    {
        return 'Instamojo';
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
        return ['api_key', 'auth_token'];
    }

    /** API base for the active environment. */
    protected function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://test.instamojo.com/api/1.1'
            : 'https://www.instamojo.com/api/1.1';
    }

    /** Http client carrying the Instamojo auth headers. */
    protected function client()
    {
        return $this->http()->withHeaders([
            'X-Api-Key'    => (string) $this->config('api_key'),
            'X-Auth-Token' => (string) $this->config('auth_token'),
        ]);
    }

    public function initiate(Payment $payment): array
    {
        $response = $this->client()->asForm()->post($this->baseUrl() . '/payment-requests/', [
            'purpose'                 => 'Subscription',
            'amount'                  => number_format((float) $payment->amount, 2, '.', ''),
            'buyer_name'              => (string) $payment->payer_name,
            'email'                   => (string) $payment->payer_email,
            'redirect_url'            => $this->callbackUrl($payment),
            'send_email'              => false,
            'allow_repeated_payments' => false,
        ]);

        if (! $response->successful()) {
            $this->fail('Failed to create payment request (HTTP ' . $response->status() . ').');
        }

        $data    = $response->json();
        $success = $data['success'] ?? false;
        $request = $data['payment_request'] ?? null;

        if ($success !== true || ! is_array($request) || empty($request['longurl'])) {
            $this->fail('Payment request was not created.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'payment_request_id' => $request['id'] ?? null,
            ]),
        ]);

        return [
            'type' => 'redirect',
            'url'  => $request['longurl'],
        ];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $paymentId        = $request->input('payment_id');
        $paymentRequestId = $request->input('payment_request_id')
            ?? data_get($payment->payload, 'payment_request_id');

        if (blank($paymentRequestId)) {
            return false;
        }

        $response = $this->client()->get(
            $this->baseUrl() . '/payment-requests/' . $paymentRequestId . '/'
        );

        if (! $response->successful()) {
            return false;
        }

        $data     = $response->json();
        $payments = data_get($data, 'payment_request.payments', []);

        if (! is_array($payments)) {
            return false;
        }

        foreach ($payments as $p) {
            if (! is_array($p)) {
                continue;
            }

            // Match the credited payment; honour the callback's payment_id when present.
            $statusOk = ($p['status'] ?? null) === 'Credit';
            $idMatch  = blank($paymentId) || ($p['payment_id'] ?? null) === $paymentId;

            if ($statusOk && $idMatch) {
                $payment->update(['gateway_ref' => $p['payment_id'] ?? $paymentId]);
                return true;
            }
        }

        return false;
    }
}
