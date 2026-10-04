<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentIntent;
use App\Services\Payments\AbstractGateway;
use Illuminate\Http\Request;

/**
 * SSLCOMMERZ hosted checkout (Bangladesh) — cards, net banking and the local
 * mobile wallets behind one aggregator.
 *
 * Flow:
 *   initiate → POST the v4 init API → redirect the payer to GatewayPageURL
 *   verify   → the provider POSTs back → re-validate server-side against the
 *              validation API before confirming
 *
 * Uses only Laravel's Http client — no SDK package. Credentials: Settings →
 * Payment Gateways (`sslcommerz_store_id`, `sslcommerz_store_passwd`,
 * `sslcommerz_sandbox`, `sslcommerz_status`).
 */
class SslcommerzGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'sslcommerz';
    }

    public function label(): string
    {
        return 'SSLCOMMERZ';
    }

    public function receiptMethod(): string
    {
        // An aggregator settles into the bank; 'Card' is the ledger's bank-side
        // method, so the money lands in the right account.
        return 'Card';
    }

    protected function requiredKeys(): array
    {
        return ['store_id', 'store_passwd'];
    }

    private function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initiate(PaymentIntent $intent): array
    {
        $callback = $this->callbackUrl($intent);
        $customer = $intent->customer;

        $response = $this->http()->asForm()->post(
            $this->baseUrl() . '/gwprocess/v4/api.php',
            [
                'store_id'         => $this->config('store_id'),
                'store_passwd'     => $this->config('store_passwd'),
                'total_amount'     => $intent->amount,
                'currency'         => $intent->currency ?: 'BDT',
                'tran_id'          => $intent->reference,
                'success_url'      => $callback,
                'fail_url'         => $callback,
                'cancel_url'       => $callback,
                'ipn_url'          => $callback,
                'cus_name'         => $customer->name ?? 'Customer',
                'cus_email'        => $customer->email ?? 'customer@example.com',
                'cus_phone'        => $customer->phone ?? '01700000000',
                'cus_add1'         => $customer->address ?? 'N/A',
                'cus_city'         => 'Dhaka',
                'cus_country'      => 'Bangladesh',
                'shipping_method'  => 'NO',
                'product_name'     => 'Travel service',
                'product_category' => 'Travel',
                'product_profile'  => 'travel-vertical',
            ]
        );

        if (! $response->successful()) {
            $this->fail('Init request failed (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();

        if (! is_array($data) || ($data['status'] ?? null) !== 'SUCCESS') {
            $this->fail('Init rejected: ' . ($data['failedreason'] ?? ($data['status'] ?? 'unknown error')));
        }

        $gatewayUrl = $data['GatewayPageURL'] ?? null;

        if (blank($gatewayUrl)) {
            $this->fail('No GatewayPageURL returned.');
        }

        $intent->rememberPayload(['sslcommerz_sessionkey' => $data['sessionkey'] ?? null]);

        return ['type' => 'redirect', 'url' => $gatewayUrl];
    }

    public function verify(Request $request, PaymentIntent $intent): bool
    {
        $status = $request->input('status');

        if ($status === 'CANCELLED') {
            return $this->reject($intent, 'Payer cancelled at SSLCOMMERZ.');
        }
        if ($status !== 'VALID' && $status !== 'VALIDATED') {
            return $this->reject($intent, "Callback status was {$status}.");
        }

        $valId = $request->input('val_id');
        if (blank($valId)) {
            return $this->reject($intent, 'Callback carried no val_id to validate.');
        }

        // The POST above is attacker-controllable; only the validation API is
        // authoritative about whether money moved.
        $response = $this->http()->get(
            $this->baseUrl() . '/validator/api/validationserverAPI.php',
            [
                'val_id'       => $valId,
                'store_id'     => $this->config('store_id'),
                'store_passwd' => $this->config('store_passwd'),
                'format'       => 'json',
            ]
        );

        if (! $response->successful()) {
            return $this->reject($intent, 'Validation call failed (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();

        if (! is_array($data)) {
            return $this->reject($intent, 'Validation returned no JSON.');
        }

        $vStatus = $data['status'] ?? null;
        if ($vStatus !== 'VALID' && $vStatus !== 'VALIDATED') {
            return $this->reject($intent, "Validation says {$vStatus}.");
        }

        // The validated transaction must be the one we started, for the amount
        // we asked, in the currency we asked.
        if (($data['tran_id'] ?? null) !== $intent->reference) {
            return $this->reject($intent, 'Validated transaction belongs to a different reference.');
        }

        $paid = (float) ($data['amount'] ?? 0);
        if (round($paid, 2) < round((float) $intent->amount, 2)) {
            return $this->reject($intent, "Validated amount {$paid} is short of the intent.");
        }

        if (($data['currency'] ?? $intent->currency) !== $intent->currency) {
            return $this->reject($intent, 'Validated currency does not match the intent.');
        }

        $intent->update(['gateway_ref' => $data['bank_tran_id'] ?? $valId]);

        return true;
    }
}
