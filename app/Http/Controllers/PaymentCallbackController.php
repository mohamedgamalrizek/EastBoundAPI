<?php

namespace App\Http\Controllers;

use App\Models\PaymentIntent;
use App\Services\Payments\PaymentIntentService;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Where payment providers send the payer (and their own servers) back to.
 *
 * Public by design — the request arrives from the provider with no session of
 * ours — and safe because nothing in it is believed: the gateway re-checks the
 * transaction against the provider's API before the intent can settle.
 *
 * The same URL serves the browser redirect and the provider's server-to-server
 * IPN. A browser gets a result page; a machine gets JSON.
 */
class PaymentCallbackController extends Controller
{
    public function __construct(
        private PaymentManager $gateways,
        private PaymentIntentService $intents,
    ) {}

    public function handle(Request $request, string $gateway, string $reference)
    {
        if (! $this->gateways->has($gateway)) {
            return $this->result($request, false, 'Unknown payment gateway.', null);
        }

        $intent = PaymentIntent::where('reference', $reference)
            ->where('gateway', $gateway)
            ->first();

        if (! $intent) {
            Log::channel('payments')->warning('callback for an unknown reference', [
                'gateway'   => $gateway,
                'reference' => $reference,
            ]);

            return $this->result($request, false, 'This payment could not be found.', null);
        }

        // A second callback for an already-settled payment is normal (the
        // browser redirect and the IPN both arrive). Report the same success
        // rather than an error, but do not settle anything twice.
        if ($intent->isPaid()) {
            return $this->result($request, true, 'This payment is already confirmed.', $intent);
        }

        $confirmed = $this->intents->confirm($request, $intent, $this->gateways->get($gateway));

        return $this->result(
            $request,
            $confirmed,
            $confirmed
                ? 'Payment confirmed.'
                : ($intent->failure_reason ?: 'The payment could not be confirmed.'),
            $intent
        );
    }

    /**
     * JSON for the provider's server, a page for the payer's browser. The app
     * opens the checkout in a web view and watches for this URL, so the page
     * also carries the outcome in a data attribute it can read.
     */
    private function result(Request $request, bool $success, string $message, ?PaymentIntent $intent)
    {
        $payload = [
            'success'   => $success,
            'message'   => $message,
            'reference' => $intent?->reference,
            'status'    => $intent?->status,
            'amount'    => $intent ? (float) $intent->amount : null,
            // A checkout the app started gets handed back to it: the page
            // fires the flow:// deep link and shows a return button.
            'from_app'  => ($intent?->payload['channel'] ?? null) === 'app',
        ];

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json($payload, $success ? 200 : 402);
        }

        // The deep link the result page fires to bring the payer back into
        // the app. Assembled here so the scheme lives in one place.
        $payload['appLink'] = 'flow://payment-result?' . http_build_query([
            'status'    => $success ? 'paid' : ($intent?->status ?: 'failed'),
            'reference' => $intent?->reference,
        ]);

        return response()->view('payment.result', $payload, $success ? 200 : 402);
    }
}
