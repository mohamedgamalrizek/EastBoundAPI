<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * bKash Tokenized Checkout (PGW).
 *
 * Flow:
 *   initiate → grant token → create payment → redirect to bkashURL
 *   verify   → query callback status → grant token → execute payment → confirm
 *
 * Uses ONLY the official bKash REST API via $this->http() (no SDK packages).
 */
class BkashGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'bkash';
    }

    public function label(): string
    {
        return 'bKash';
    }

    public function region(): string
    {
        return 'Bangladesh';
    }

    public function currencies(): array
    {
        return ['BDT'];
    }

    protected function requiredKeys(): array
    {
        return ['app_key', 'app_secret', 'username', 'password'];
    }

    /** Base URL for the Tokenized Checkout API. */
    protected function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    /**
     * Grant an id_token from the bKash auth endpoint.
     * Returns the token string or null on failure.
     */
    protected function grantToken(): ?string
    {
        $response = $this->http()
            ->withHeaders([
                'username' => (string) $this->config('username'),
                'password' => (string) $this->config('password'),
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/token/grant', [
                'app_key'    => (string) $this->config('app_key'),
                'app_secret' => (string) $this->config('app_secret'),
            ]);

        if (! $response->successful()) {
            return null;
        }

        $token = $response->json('id_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function initiate(Payment $payment): array
    {
        // (1) Grant token.
        $token = $this->grantToken();
        if ($token === null) {
            $this->fail('Unable to grant access token.');
        }

        // (2) Create the payment.
        $response = $this->http()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => (string) $this->config('app_key'),
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/create', [
                'mode'                  => '0011',
                'payerReference'        => '01',
                'callbackURL'           => $this->callbackUrl($payment),
                'amount'                => (string) $payment->amount,
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $payment->reference,
            ]);

        if (! $response->successful()) {
            $this->fail('Create payment request failed.');
        }

        $bkashUrl  = $response->json('bkashURL');
        $paymentID = $response->json('paymentID');

        if (blank($bkashUrl) || blank($paymentID)) {
            $message = $response->json('statusMessage') ?? 'Create payment returned no checkout URL.';
            $this->fail($message);
        }

        // Persist the paymentID for the verify/execute step.
        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'bkash_payment_id' => $paymentID,
            ]),
        ]);

        return [
            'type' => 'redirect',
            'url'  => $bkashUrl,
        ];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        // bKash returns to the callbackURL with query params.
        $status    = (string) $request->query('status', '');
        $paymentID = $request->query('paymentID')
            ?? (($payment->payload['bkash_payment_id'] ?? null));

        if ($status !== 'success' || blank($paymentID)) {
            return false;
        }

        // Re-grant a fresh token for the execute call.
        $token = $this->grantToken();
        if ($token === null) {
            return false;
        }

        // Execute the payment to capture funds.
        $response = $this->http()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => (string) $this->config('app_key'),
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/execute', [
                'paymentID' => $paymentID,
            ]);

        if (! $response->successful()) {
            return false;
        }

        $txnStatus  = $response->json('transactionStatus');
        $statusCode = $response->json('statusCode');
        $trxID      = $response->json('trxID');

        $paid = ($txnStatus === 'Completed') || ($statusCode === '0000');

        if (! $paid || blank($trxID)) {
            return false;
        }

        $payment->update(['gateway_ref' => $trxID]);

        return true;
    }
}
