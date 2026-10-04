<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentIntent;
use App\Services\Payments\AbstractGateway;
use Illuminate\Http\Request;

/**
 * bKash Tokenized Checkout (PGW).
 *
 * Flow:
 *   initiate → grant token → create payment → redirect to bkashURL
 *   verify   → check callback status → grant token → execute → confirm
 *
 * Uses ONLY the official bKash REST API via $this->http() (no SDK packages).
 * Credentials: Settings → Payment Gateways (`bkash_app_key`, `bkash_app_secret`,
 * `bkash_username`, `bkash_password`, `bkash_sandbox`, `bkash_status`).
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

    public function receiptMethod(): string
    {
        return 'bKash';
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

    /** Grant an id_token from the bKash auth endpoint, or null on failure. */
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

    public function initiate(PaymentIntent $intent): array
    {
        $token = $this->grantToken();
        if ($token === null) {
            $this->fail('Unable to grant access token.');
        }

        $response = $this->http()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => (string) $this->config('app_key'),
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/create', [
                'mode'                  => '0011',
                'payerReference'        => '01',
                'callbackURL'           => $this->callbackUrl($intent),
                'amount'                => (string) $intent->amount,
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $intent->reference,
            ]);

        if (! $response->successful()) {
            $this->fail('Create payment request failed.');
        }

        $bkashUrl  = $response->json('bkashURL');
        $paymentID = $response->json('paymentID');

        if (blank($bkashUrl) || blank($paymentID)) {
            $this->fail($response->json('statusMessage') ?? 'Create payment returned no checkout URL.');
        }

        // Kept for the execute step: the callback may not echo it back.
        $intent->rememberPayload(['bkash_payment_id' => $paymentID]);

        return ['type' => 'redirect', 'url' => $bkashUrl];
    }

    public function verify(Request $request, PaymentIntent $intent): bool
    {
        $status    = (string) $request->input('status', '');
        $paymentID = $request->input('paymentID') ?? ($intent->payload['bkash_payment_id'] ?? null);

        if ($status === 'cancel') {
            return $this->reject($intent, 'Payer cancelled at bKash.');
        }
        if ($status !== 'success' || blank($paymentID)) {
            return $this->reject($intent, "Callback did not report success (status: {$status}).");
        }

        $token = $this->grantToken();
        if ($token === null) {
            return $this->reject($intent, 'Unable to grant access token for execute.');
        }

        // Execute captures the funds. bKash treats a repeated execute for the
        // same paymentID as an error, so the caller must not retry a confirmed
        // intent — PaymentIntentService's status guard is what prevents that.
        $response = $this->http()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => (string) $this->config('app_key'),
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/execute', [
                'paymentID' => $paymentID,
            ]);

        if (! $response->successful()) {
            return $this->reject($intent, 'Execute call failed (HTTP ' . $response->status() . ').');
        }

        $txnStatus  = $response->json('transactionStatus');
        $statusCode = $response->json('statusCode');
        $trxID      = $response->json('trxID');

        if ((($txnStatus !== 'Completed') && ($statusCode !== '0000')) || blank($trxID)) {
            return $this->reject($intent, $response->json('statusMessage') ?? 'Execute did not complete the payment.');
        }

        // The amount bKash actually took must be the amount we asked for —
        // otherwise a tampered checkout could settle a booking for less.
        $charged = (float) ($response->json('amount') ?? 0);
        if (round($charged, 2) !== round((float) $intent->amount, 2)) {
            return $this->reject($intent, "Charged amount {$charged} does not match the intent.");
        }

        $intent->update(['gateway_ref' => $trxID]);

        return true;
    }
}
