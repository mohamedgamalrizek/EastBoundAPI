<?php

namespace Modules\Saas\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\Contracts\PaymentGateway;

/**
 * Shared plumbing for every gateway: typed config access, a configured Http
 * client, and the standard callback URL. Concrete gateways implement only the
 * provider-specific bits (key/label/initiate/verify + requiredKeys).
 */
abstract class AbstractGateway implements PaymentGateway
{
    /** Credential keys (settings `saas_<gateway>_<field>`) that must be set. */
    abstract protected function requiredKeys(): array;

    public function region(): string
    {
        return 'International';
    }

    public function currencies(): array
    {
        return ['USD'];
    }

    public function isConfigured(): bool
    {
        foreach ($this->requiredKeys() as $key) {
            if (blank($this->config($key))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Read one of this gateway's credentials.
     *
     * These live in the `settings` table, not in .env or a config file, so the
     * platform owner can go live from the panel without a redeploy — and so no
     * secret is sitting in a file that a copied backup would expose. The key is
     * prefixed with `saas_` to keep the platform's own credentials separate
     * from an agency's gateway settings, which use the bare `<gateway>_<field>`
     * names.
     */
    protected function config(string $key, $default = null)
    {
        $value = settings("saas_{$this->key()}_{$key}");

        return blank($value) ? $default : $value;
    }

    protected function sandbox(): bool
    {
        return (bool) $this->config('sandbox', true);
    }

    /** The URL the provider returns the payer to after checkout. */
    protected function callbackUrl(Payment $payment): string
    {
        return route('saas.payment.callback', [
            'gateway'   => $this->key(),
            'reference' => $payment->reference,
        ]);
    }

    /** A JSON Http client; gateways add auth/headers as the provider requires. */
    protected function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(30);
    }

    /** Convenience: throw a uniform error when a provider call fails. */
    protected function fail(string $message): void
    {
        throw new \RuntimeException("[{$this->label()}] {$message}");
    }
}
