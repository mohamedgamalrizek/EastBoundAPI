<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGateway;

/**
 * Registry of online payment gateways. The class list comes from
 * config('payments.gateways'); whether any of them can actually take money is
 * a Settings question, answered by the gateway itself.
 *
 * Bound as a singleton, so `app(PaymentManager::class)` is the one place that
 * knows which providers exist.
 */
class PaymentManager
{
    /** @var array<string, PaymentGateway> keyed by gateway->key() */
    protected array $gateways = [];

    public function __construct()
    {
        foreach ((array) config('payments.gateways', []) as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $gateway = new $class();
            $this->gateways[$gateway->key()] = $gateway;
        }
    }

    /** @return array<string, PaymentGateway> */
    public function all(): array
    {
        return $this->gateways;
    }

    /** Only the gateways the agency has switched on AND filled in. */
    public function enabled(): array
    {
        return array_filter($this->gateways, fn (PaymentGateway $g) => $g->isEnabled());
    }

    public function has(string $key): bool
    {
        return isset($this->gateways[$key]);
    }

    public function get(string $key): PaymentGateway
    {
        if (! $this->has($key)) {
            throw new \InvalidArgumentException("Unknown payment gateway [{$key}].");
        }

        return $this->gateways[$key];
    }

    /** The enabled gateway a payer's chosen method maps to, or null for offline methods. */
    public function forMethod(?string $method): ?PaymentGateway
    {
        if (blank($method)) {
            return null;
        }

        $method = strtolower($method);

        foreach ($this->enabled() as $gateway) {
            if ($method === $gateway->key() || $method === strtolower($gateway->receiptMethod())) {
                return $gateway;
            }
        }

        return null;
    }

    /** Payment options the app and the portal should offer, online ones first. */
    public function optionsForCheckout(): array
    {
        $options = [];

        foreach ($this->enabled() as $gateway) {
            $options[] = [
                'key'    => $gateway->key(),
                'label'  => $gateway->label(),
                'online' => true,
            ];
        }

        return $options;
    }
}
