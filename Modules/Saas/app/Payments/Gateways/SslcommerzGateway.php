<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * SSLCOMMERZ hosted checkout (Bangladesh).
 *
 * Flow:
 *   initiate() POSTs to the v4 init API and redirects the payer to the
 *   GatewayPageURL it returns. After payment the provider POSTs back to our
 *   callback URL; verify() re-validates the transaction server-side against the
 *   validation API before confirming it as paid.
 *
 * Uses only Laravel's Http client — no SDK package.
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
        return ['store_id', 'store_passwd'];
    }

    /** Base host for the SSLCOMMERZ APIs (sandbox vs live). */
    private function baseUrl(): string
    {
        return $this->sandbox()
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initiate(Payment $payment): array
    {
        $callback = $this->callbackUrl($payment);

        $response = $this->http()->asForm()->post(
            $this->baseUrl() . '/gwprocess/v4/api.php',
            [
                'store_id'         => $this->config('store_id'),
                'store_passwd'     => $this->config('store_passwd'),
                'total_amount'     => $payment->amount,
                'currency'         => $payment->currency ?: 'BDT',
                'tran_id'          => $payment->reference,
                'success_url'      => $callback,
                'fail_url'         => $callback,
                'cancel_url'       => $callback,
                'cus_name'         => $payment->payer_name ?: 'Customer',
                'cus_email'        => $payment->payer_email ?: 'customer@example.com',
                'cus_phone'        => '01700000000',
                'cus_add1'         => 'N/A',
                'cus_city'         => 'Dhaka',
                'cus_country'      => 'Bangladesh',
                'shipping_method'  => 'NO',
                'product_name'     => 'Subscription',
                'product_category' => 'SaaS',
                'product_profile'  => 'general',
            ]
        );

        if (! $response->successful()) {
            $this->fail('Init request failed (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();

        if (! is_array($data) || ($data['status'] ?? null) !== 'SUCCESS') {
            $reason = $data['failedreason'] ?? ($data['status'] ?? 'unknown error');
            $this->fail('Init rejected: ' . $reason);
        }

        $gatewayUrl = $data['GatewayPageURL'] ?? null;

        if (blank($gatewayUrl)) {
            $this->fail('No GatewayPageURL returned.');
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'sslcommerz_sessionkey' => $data['sessionkey'] ?? null,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $gatewayUrl];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $status = $request->input('status');

        if ($status !== 'VALID' && $status !== 'VALIDATED') {
            return false;
        }

        $valId = $request->input('val_id');

        if (blank($valId)) {
            return false;
        }

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
            return false;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return false;
        }

        $vStatus = $data['status'] ?? null;

        if ($vStatus !== 'VALID' && $vStatus !== 'VALIDATED') {
            return false;
        }

        // Confirm the validated transaction is the one we initiated.
        if (($data['tran_id'] ?? null) !== $payment->reference) {
            return false;
        }

        $txnId = $data['bank_tran_id'] ?? $valId;

        $payment->update(['gateway_ref' => $txnId]);

        return true;
    }
}
