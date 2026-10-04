<?php

namespace Modules\Saas\Payments\Gateways;

use Illuminate\Http\Request;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\AbstractGateway;

/**
 * PayU India — hosted checkout via form-POST.
 *
 * No SDK: the payer's browser is POSTed to PayU's _payment endpoint with a
 * SHA-512 request hash; PayU returns to our callback with a response hash we
 * recompute (reverse order) and compare with hash_equals().
 */
class PayuGateway extends AbstractGateway
{
    public function key(): string
    {
        return 'payu';
    }

    public function label(): string
    {
        return 'PayU';
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
        return ['merchant_key', 'merchant_salt'];
    }

    /** PayU _payment endpoint for the active environment. */
    protected function endpoint(): string
    {
        return $this->sandbox()
            ? 'https://test.payu.in/_payment'
            : 'https://secure.payu.in/_payment';
    }

    public function initiate(Payment $payment): array
    {
        $key  = (string) $this->config('merchant_key');
        $salt = (string) $this->config('merchant_salt');

        $txnid       = (string) $payment->reference;
        $amount      = number_format((float) $payment->amount, 2, '.', '');
        $productinfo = 'Subscription';
        $firstname   = (string) ($payment->payer_name ?? '');
        $email       = (string) ($payment->payer_email ?? '');
        $callback    = $this->callbackUrl($payment);

        // hash = sha512(key|txnid|amount|productinfo|firstname|email|||||||||||salt)
        $hashString = implode('|', [
            $key, $txnid, $amount, $productinfo, $firstname, $email,
        ]) . '|||||||||||' . $salt;
        $hash = hash('sha512', $hashString);

        $payment->update([
            'payload' => array_merge((array) $payment->payload, [
                'payu_txnid' => $txnid,
            ]),
        ]);

        return [
            'type'   => 'post',
            'url'    => $this->endpoint(),
            'fields' => [
                'key'         => $key,
                'txnid'       => $txnid,
                'amount'      => $amount,
                'productinfo' => $productinfo,
                'firstname'   => $firstname,
                'email'       => $email,
                'phone'       => '9999999999',
                'surl'        => $callback,
                'furl'        => $callback,
                'hash'        => $hash,
            ],
        ];
    }

    public function verify(Request $request, Payment $payment): bool
    {
        $status = (string) $request->input('status', '');
        if ($status !== 'success') {
            return false;
        }

        $key  = (string) $this->config('merchant_key');
        $salt = (string) $this->config('merchant_salt');

        $txnid       = (string) $request->input('txnid', '');
        $amount      = (string) $request->input('amount', '');
        $productinfo = (string) $request->input('productinfo', '');
        $firstname   = (string) $request->input('firstname', '');
        $email       = (string) $request->input('email', '');
        $mihpayid    = (string) $request->input('mihpayid', '');
        $postedHash  = (string) $request->input('hash', '');

        if ($postedHash === '' || $mihpayid === '') {
            return false;
        }

        // Reverse hash:
        //   sha512(salt|status|||||||||||email|firstname|productinfo|amount|txnid|key)
        // If PayU sent additionalCharges, it is prepended to the reverse string.
        $additional = $request->input('additionalCharges');

        $base = implode('|', [
            $salt, $status,
        ]) . '|||||||||||' . implode('|', [
            $email, $firstname, $productinfo, $amount, $txnid, $key,
        ]);

        if ($additional !== null && $additional !== '') {
            $base = $additional . '|' . $base;
        }

        $reverseHash = hash('sha512', $base);

        if (! hash_equals($reverseHash, $postedHash)) {
            return false;
        }

        $payment->update(['gateway_ref' => $mihpayid]);

        return true;
    }
}
