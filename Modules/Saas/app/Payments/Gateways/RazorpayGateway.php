<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Razorpay (India) via the official Payment Links REST API.
 *
 * Docs: https://api.razorpay.com/v1/payment_links
 * Auth: HTTP Basic (key_id : key_secret).
 */
class RazorpayGateway extends AbstractGateway
{
    private const BASE = 'https://api.razorpay.com/v1';

    public function key(): string
    {
        return 'razorpay';
    }

    public function label(): string
    {
        return 'Razorpay';
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
        return ['key_id', 'key_secret'];
    }

    public function initiate(Payment $payment): array
    {
        $response = $this->http()
            ->withBasicAuth($this->config('key_id'), $this->config('key_secret'))
            ->post(self::BASE . '/payment_links', [
                'amount'          => (int) round(((float) $payment->amount) * 100),
                'currency'        => 'INR',
                'accept_partial'  => false,
                'reference_id'    => $payment->reference,
                'description'     => 'Subscription',
                'customer'        => [
                    'name'  => $payment->payer_name,
                    'email' => $payment->payer_email,
                ],
                'notify'          => ['email' => true],
                'callback_url'    => $this->callbackUrl($payment),
                'callback_method' => 'get',
            ]);

        if (! $response->successful()) {
            $error = $response->json('error.description') ?? $response->body();
            $this->fail('Payment link creation failed: ' . $error);
        }

        $shortUrl = $response->json('short_url');
        $linkId   = $response->json('id');

        if (blank($shortUrl)) {
            $this->fail('Provider did not return a payment link URL.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'razorpay_payment_link_id' => $linkId,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $shortUrl];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $paymentId   = $request->query('razorpay_payment_id');
        $linkId      = $request->query('razorpay_payment_link_id');
        $referenceId = $request->query('razorpay_payment_link_reference_id');
        $status      = $request->query('razorpay_payment_link_status');
        $signature   = $request->query('razorpay_signature');

        if ($status !== 'paid') {
            return false;
        }

        if (blank($paymentId) || blank($linkId) || blank($signature)) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $linkId . '|' . $referenceId . '|' . $status . '|' . $paymentId,
            (string) $this->config('key_secret')
        );

        if (! hash_equals($expected, (string) $signature)) {
            return false;
        }

        $payment->update(['gateway_ref' => $paymentId]);

        return true;
    }
}
