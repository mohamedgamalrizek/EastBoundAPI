<?php

namespace App\Traits;

use App\Services\Payments\PaymentIntentService;
use App\Services\Payments\PaymentManager;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared entry into online checkout for the pay endpoints.
 *
 * A payment method is only "online" when the agency has that gateway switched
 * on and filled in. Otherwise this returns null and the caller carries on with
 * the record-payment flow it has always had — which is what keeps a fresh
 * install (no credentials anywhere) working exactly as before.
 */
trait StartsOnlinePayment
{
    /**
     * @return array|null  ['gateway','reference','redirect_url','callback_url'] or null
     *                     when the chosen method is not a live gateway.
     *
     * @throws \RuntimeException when the provider refuses to start the checkout.
     */
    protected function startOnlinePayment(Model $payable, ?string $method, ?int $customerId = null): ?array
    {
        $gateway = app(PaymentManager::class)->forMethod($method);

        if (! $gateway) {
            return null;
        }

        $service = app(PaymentIntentService::class);
        // 'app' — this trait only serves the mobile API's pay endpoints, and
        // the result page uses the channel to hand the payer back to the app.
        $intent  = $service->createFor($payable, $gateway, $customerId, 'app');
        $charge  = $service->start($intent, $gateway);

        return [
            'online'       => true,
            'gateway'      => $gateway->key(),
            'label'        => $gateway->label(),
            'reference'    => $intent->reference,
            'amount'       => (float) $intent->amount,
            'redirect_url' => $charge['url'],
            // The app watches for this URL in its web view to know the payer
            // is back; it carries the outcome in the page's data attributes.
            'callback_url' => route('payment.callback', [
                'gateway'   => $gateway->key(),
                'reference' => $intent->reference,
            ]),
        ];
    }

    /** The online options to advertise alongside the offline ones. */
    protected function onlinePaymentOptions(): array
    {
        return app(PaymentManager::class)->optionsForCheckout();
    }
}
