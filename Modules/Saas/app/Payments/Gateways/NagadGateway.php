<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * Nagad DFS (Digital Financial Service) gateway — package-free.
 *
 * Crypto model (no SDK): sensitive payloads are encrypted with Nagad's PUBLIC
 * key and every request is signed with the merchant's PRIVATE key (SHA256).
 * Responses returned by Nagad are encrypted with the merchant public key and
 * decrypted here with the merchant private key.
 */
class NagadGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'nagad';
    }

    public function label(): string
    {
        return 'Nagad';
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
        return ['merchant_id', 'merchant_number', 'public_key', 'private_key'];
    }

    /** API root for the current environment. */
    protected function baseUrl(): string
    {
        return $this->sandbox()
            ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs'
            : 'https://api.mynagad.com/api/dfs';
    }

    /** Normalise a config key/cert into a usable PEM string. */
    protected function pem(string $raw, string $type): string
    {
        $raw = trim($raw);

        // If headers already present, keep as-is.
        if (str_contains($raw, '-----BEGIN')) {
            return $raw;
        }

        $header = $type === 'public'
            ? 'PUBLIC KEY'
            : 'PRIVATE KEY';

        return "-----BEGIN {$header}-----\n"
            . chunk_split($raw, 64, "\n")
            . "-----END {$header}-----\n";
    }

    /** Encrypt JSON-able data with Nagad's public key → base64. */
    protected function rsaEncrypt(string $plain): string
    {
        $publicKey = openssl_pkey_get_public($this->pem((string) $this->config('public_key'), 'public'));
        if ($publicKey === false) {
            $this->fail('Invalid Nagad public key configuration.');
        }

        $encrypted = '';
        if (! openssl_public_encrypt($plain, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
            $this->fail('Unable to RSA-encrypt request payload.');
        }

        return base64_encode($encrypted);
    }

    /** Sign data with the merchant private key (SHA256) → base64. */
    protected function rsaSign(string $plain): string
    {
        $privateKey = openssl_pkey_get_private($this->pem((string) $this->config('private_key'), 'private'));
        if ($privateKey === false) {
            $this->fail('Invalid Nagad merchant private key configuration.');
        }

        $signature = '';
        if (! openssl_sign($plain, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            $this->fail('Unable to sign request payload.');
        }

        return base64_encode($signature);
    }

    /** Decrypt a Nagad response field with the merchant private key. */
    protected function rsaDecrypt(string $base64Cipher): array
    {
        $privateKey = openssl_pkey_get_private($this->pem((string) $this->config('private_key'), 'private'));
        if ($privateKey === false) {
            $this->fail('Invalid Nagad merchant private key configuration.');
        }

        $decrypted = '';
        $cipher = base64_decode($base64Cipher, true);
        if ($cipher === false || ! openssl_private_decrypt($cipher, $decrypted, $privateKey, OPENSSL_PKCS1_PADDING)) {
            $this->fail('Unable to decrypt Nagad response payload.');
        }

        return json_decode($decrypted, true) ?: [];
    }

    /** Standard Nagad request headers. */
    protected function headers(): array
    {
        return [
            'X-KM-Api-Version'  => 'v-0.2.0',
            'X-KM-IP-V4'        => request()->ip() ?? '127.0.0.1',
            'X-KM-Client-Type'  => 'PC_WEB',
            'Content-Type'      => 'application/json',
        ];
    }

    public function initiate(Payment $payment): array
    {
        if (! \function_exists('openssl_public_encrypt')) {
            $this->fail('OpenSSL extension is required for Nagad payments.');
        }

        $merchantId = (string) $this->config('merchant_id');
        $orderId    = (string) $payment->reference;
        $base       = $this->baseUrl();
        $dateTime   = now()->format('YmdHis');

        // ---- (1) INITIALIZE -------------------------------------------------
        $initSensitive = json_encode([
            'merchantId' => $merchantId,
            'datetime'   => $dateTime,
            'orderId'    => $orderId,
            'challenge'  => bin2hex(random_bytes(20)),
        ]);

        $initResponse = $this->http()
            ->withHeaders($this->headers())
            ->post("{$base}/check-out/initialize/{$merchantId}/{$orderId}", [
                'accountNumber' => (string) $this->config('merchant_number'),
                'dateTime'      => $dateTime,
                'sensitiveData' => $this->rsaEncrypt($initSensitive),
                'signature'     => $this->rsaSign($initSensitive),
            ]);

        if (! $initResponse->successful()) {
            $this->fail('Initialize request failed: HTTP ' . $initResponse->status());
        }

        $initBody = $initResponse->json() ?? [];
        if (($initBody['status'] ?? null) !== 'Success' || empty($initBody['sensitiveData'])) {
            $reason = $initBody['message'] ?? $initBody['reason'] ?? 'unknown error';
            $this->fail('Initialize rejected by Nagad: ' . $reason);
        }

        $initDecrypted     = $this->rsaDecrypt($initBody['sensitiveData']);
        $paymentReferenceId = $initDecrypted['paymentReferenceId'] ?? null;
        $challenge          = $initDecrypted['challenge'] ?? null;

        if (! $paymentReferenceId || ! $challenge) {
            $this->fail('Initialize response missing paymentReferenceId/challenge.');
        }

        // ---- (2) COMPLETE / CONFIRM ----------------------------------------
        $completeSensitive = json_encode([
            'merchantId'   => $merchantId,
            'orderId'      => $orderId,
            'amount'       => (string) $payment->amount,
            'currencyCode' => '050', // BDT
            'challenge'    => $challenge,
        ]);

        $completeResponse = $this->http()
            ->withHeaders($this->headers())
            ->post("{$base}/check-out/complete/{$paymentReferenceId}", [
                'sensitiveData'       => $this->rsaEncrypt($completeSensitive),
                'signature'           => $this->rsaSign($completeSensitive),
                'merchantCallbackURL' => $this->callbackUrl($payment),
            ]);

        if (! $completeResponse->successful()) {
            $this->fail('Complete request failed: HTTP ' . $completeResponse->status());
        }

        $completeBody = $completeResponse->json() ?? [];
        $redirectUrl  = $completeBody['callBackUrl'] ?? null;

        if (($completeBody['status'] ?? null) !== 'Success' || ! $redirectUrl) {
            $reason = $completeBody['message'] ?? $completeBody['reason'] ?? 'no callBackUrl returned';
            $this->fail('Complete rejected by Nagad: ' . $reason);
        }

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'nagad_payment_reference_id' => $paymentReferenceId,
                'nagad_challenge'            => $challenge,
            ]),
        ]);

        return ['type' => 'redirect', 'url' => $redirectUrl];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $paymentRefId = $request->query('payment_ref_id')
            ?? $request->query('paymentRefId')
            ?? data_get($payment->payload, 'nagad_payment_reference_id');

        if (! $paymentRefId) {
            return false;
        }

        $verifyResponse = $this->http()
            ->withHeaders($this->headers())
            ->get("{$this->baseUrl()}/verify/payment/{$paymentRefId}");

        if (! $verifyResponse->successful()) {
            return false;
        }

        $body = $verifyResponse->json() ?? [];

        if (($body['status'] ?? null) !== 'Success') {
            return false;
        }

        $txnId = $body['issuerPaymentRefNo']
            ?? $body['paymentRefId']
            ?? $paymentRefId;

        $payment->update(['gateway_ref' => $txnId]);

        return true;
    }
}
