<?php

namespace App\Services\Payments;

use App\Models\PaymentIntent;
use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shared plumbing for every gateway: credentials, an Http client and the
 * callback URL. Concrete gateways implement only the provider-specific parts.
 *
 * Credentials come from Settings (`<key>_<field>`, e.g. `bkash_app_key`), not
 * from .env — the buyer types them into the admin panel, the same way the SMS
 * gateway works, so no deploy is needed to go live.
 */
abstract class AbstractGateway implements PaymentGateway
{
    /** Settings fields (without the `<key>_` prefix) that must all be filled. */
    abstract protected function requiredKeys(): array;

    public function currencies(): array
    {
        return ['BDT'];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config('status') && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        foreach ($this->requiredKeys() as $key) {
            $value = $this->config($key);

            // 'xxx' is what the seeder ships as a placeholder; treat it as blank
            // so a fresh install never reports itself ready to take money.
            if (blank($value) || $value === 'xxx') {
                return false;
            }
        }

        return true;
    }

    /** One of this gateway's settings values, e.g. config('app_key') → `bkash_app_key`. */
    protected function config(string $field, $default = null)
    {
        $value = settings("{$this->key()}_{$field}");

        return ($value === null || $value === '') ? $default : $value;
    }

    protected function sandbox(): bool
    {
        return (bool) $this->config('sandbox', true);
    }

    /** The URL the provider returns the payer to after checkout. */
    protected function callbackUrl(PaymentIntent $intent): string
    {
        return route('payment.callback', [
            'gateway'   => $this->key(),
            'reference' => $intent->reference,
        ]);
    }

    /** A JSON Http client; gateways add auth/headers as the provider requires. */
    protected function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(30);
    }

    /** Uniform failure for an initiate() that cannot produce a checkout URL. */
    protected function fail(string $message): void
    {
        throw new \RuntimeException("[{$this->label()}] {$message}");
    }

    /**
     * Why a verify() said no. Logged rather than shown: the payer gets a plain
     * "payment could not be confirmed", while the agency can see the provider's
     * own words in storage/logs/payments.log.
     */
    protected function reject(PaymentIntent $intent, string $reason): bool
    {
        Log::channel('payments')->warning("[{$this->label()}] {$reason}", [
            'reference' => $intent->reference,
            'intent_id' => $intent->id,
        ]);

        $intent->update(['failure_reason' => $reason]);

        return false;
    }
}
