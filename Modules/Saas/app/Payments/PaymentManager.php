<?php

namespace Modules\Saas\Payments;

use Modules\Saas\Payments\Contracts\PaymentGateway;

/**
 * Registry of payment gateways. Loads the class list from config('payment.enabled')
 * and exposes lookups. Bound as a singleton in SaasServiceProvider, so the whole
 * payment layer is `app(PaymentManager::class)` anywhere — and portable: copy the
 * module into another app and it just works.
 */
class PaymentManager
{
    /** @var array<string, PaymentGateway> keyed by gateway->key() */
    protected array $gateways = [];

    public function __construct()
    {
        foreach ((array) config('payment.enabled', []) as $class) {
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

    /** Only gateways whose credentials are set. @return array<string, PaymentGateway> */
    public function configured(): array
    {
        return array_filter($this->gateways, fn (PaymentGateway $g) => $g->isConfigured());
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

    /** Gateways grouped by region for tidy display. @return array<string, array<string,PaymentGateway>> */
    public function byRegion(): array
    {
        $grouped = [];
        foreach ($this->gateways as $key => $gateway) {
            $grouped[$gateway->region()][$key] = $gateway;
        }
        return $grouped;
    }
}
