<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * aamarPay (Bangladesh) — package-free integration over the official JSON API.
 *
 * Flow:
 *   initiate() → POST JSON to jsonpost.php → response carries 'payment_url'
 *                (sometimes relative; we prefix the host when needed).
 *   verify()   → on callback, GET the trxcheck endpoint to confirm the txn is
 *                'Successful' before we trust it; capture pg_txnid as gateway_ref.
 */
class AamarpayGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'aamarpay';
    }

    public function label(): string
    {
        return 'aamarPay';
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
        return ['store_id', 'signature_key'];
    }

    /** Provider host (no trailing slash). */
    private function host(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.aamarpay.com'
            : 'https://secure.aamarpay.com';
    }

    public function initiate(Payment $payment): array
    {
        $callback = $this->callbackUrl($payment);

        $response = $this->http()->post($this->host() . '/jsonpost.php', [
            'store_id'      => (string) $this->config('store_id'),
            'signature_key' => (string) $this->config('signature_key'),
            'tran_id'       => $payment->reference,
            'amount'        => (string) $payment->amount,
            'currency'      => $payment->currency ?: 'BDT',
            'desc'          => 'Subscription',
            'cus_name'      => $payment->payer_name ?: 'Customer',
            'cus_email'     => $payment->payer_email ?: 'customer@example.com',
            'cus_phone'     => '01700000000',
            'success_url'   => $callback,
            'fail_url'      => $callback,
            'cancel_url'    => $callback,
            'type'          => 'json',
        ]);

        if (! $response->successful()) {
            $this->fail('Failed to initiate payment (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();
        $paymentUrl = is_array($data) ? ($data['payment_url'] ?? null) : null;

        if (blank($paymentUrl)) {
            $reason = is_array($data) ? ($data['reason'] ?? $data['result'] ?? null) : null;
            $this->fail('No payment_url returned' . ($reason ? ': ' . $reason : '.'));
        }

        // aamarPay sometimes returns a relative path; make it absolute.
        if (! str_starts_with($paymentUrl, 'http')) {
            $paymentUrl = $this->host() . '/' . ltrim($paymentUrl, '/');
        }

        return ['type' => 'redirect', 'url' => $paymentUrl];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $response = $this->http()->get($this->host() . '/api/v1/trxcheck/request.php', [
            'request_id'    => $payment->reference,
            'store_id'      => (string) $this->config('store_id'),
            'signature_key' => (string) $this->config('signature_key'),
            'type'          => 'json',
        ]);

        if (! $response->successful()) {
            return false;
        }

        $data = $response->json();
        if (! is_array($data)) {
            return false;
        }

        $status = $data['pay_status'] ?? null;

        if ($status !== 'Successful') {
            return false;
        }

        $txnId = $data['pg_txnid'] ?? ($request->input('pg_txnid') ?? null);
        if (! blank($txnId)) {
            $payment->update(['gateway_ref' => $txnId]);
        }

        return true;
    }
}
