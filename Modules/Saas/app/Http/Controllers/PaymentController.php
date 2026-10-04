<?php

namespace Modules\Saas\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Saas\Models\Plan;
use Modules\Saas\Models\Payment;
use Modules\Saas\Payments\PaymentManager;

/**
 * The package-free payment flow. Uniform across all 12 gateways:
 *   checkout → pay (initiate) → provider page → callback (verify) → result.
 */
class PaymentController extends Controller
{
    protected PaymentManager $manager;

    public function __construct(PaymentManager $manager)
    {
        $this->manager = $manager;
    }

    /* ---- Admin: gateway directory ---- */

    public function gateways()
    {
        return view('saas::saas.gateways', ['regions' => $this->manager->byRegion()]);
    }

    public function records()
    {
        return view('saas::saas.payment-records', [
            'payments' => Payment::with('plan')->latest()->take(200)->get(),
            'collected' => (float) Payment::where('status', 'success')->sum('amount'),
        ]);
    }

    /* ---- Public checkout ---- */

    public function checkout($plan)
    {
        return view('saas::saas.checkout', [
            'plan'     => Plan::findOrFail($plan),
            'gateways' => $this->manager->configured() ?: $this->manager->all(),
            'demo'     => empty($this->manager->configured()),
        ]);
    }

    public function pay(Request $request)
    {
        $request->validate([
            'plan_id'     => ['required', 'exists:plans,id'],
            'gateway'     => ['required', 'string'],
            'payer_name'  => ['required', 'string', 'max:120'],
            'payer_email' => ['required', 'email', 'max:150'],
        ]);

        if (! $this->manager->has($request->gateway)) {
            return back()->with('danger', 'Unknown payment gateway.');
        }

        $gateway = $this->manager->get($request->gateway);
        $plan    = Plan::findOrFail($request->plan_id);

        $payment = Payment::create([
            'gateway'     => $gateway->key(),
            'reference'   => Payment::makeReference(),
            'plan_id'     => $plan->id,
            'payer_name'  => $request->payer_name,
            'payer_email' => $request->payer_email,
            'amount'      => $plan->price,
            'currency'    => $plan->currency ?? config('payment.default_currency', 'BDT'),
            'status'      => 'pending',
        ]);

        if (! $gateway->isConfigured()) {
            // No live credentials: short-circuit to a clearly-labelled demo result
            // so the flow is fully walkable without secrets.
            $payment->update(['status' => 'pending', 'payload' => ['demo' => true]]);
            return redirect()->route('saas.payment.result', $payment->reference)
                ->with('info', "Demo mode: set {$gateway->label()} credentials in .env to take a real payment.");
        }

        try {
            $charge = $gateway->initiate($payment);
        } catch (\Throwable $e) {
            $payment->markFailed(['error' => $e->getMessage()]);
            return redirect()->route('saas.payment.result', $payment->reference)
                ->with('danger', $e->getMessage());
        }

        // Redirect-style gateways: send the browser straight to the provider.
        if (($charge['type'] ?? 'redirect') === 'redirect') {
            return redirect()->away($charge['url']);
        }

        // POST-style gateways (PayU, etc.): render an auto-submitting form.
        return view('saas::saas.payment-redirect', [
            'url'    => $charge['url'],
            'fields' => $charge['fields'] ?? [],
            'label'  => $gateway->label(),
        ]);
    }

    /**
     * Provider return / callback. GET or POST — providers differ. Verifies the
     * payment and routes the payer to the result page.
     */
    public function callback(Request $request, string $gateway)
    {
        $reference = $request->query('reference') ?? $request->input('reference');
        $payment   = Payment::where('reference', $reference)->firstOrFail();

        if ($payment->status !== 'success' && $this->manager->has($gateway)) {
            try {
                $paid = $this->manager->get($gateway)->verify($request, $payment);
                $paid ? $payment->markSuccess($payment->gateway_ref, ['callback' => $request->all()])
                      : $payment->markFailed(['callback' => $request->all()]);
            } catch (\Throwable $e) {
                $payment->markFailed(['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('saas.payment.result', $payment->reference);
    }

    public function result($reference)
    {
        return view('saas::saas.payment-result', [
            'payment' => Payment::where('reference', $reference)->firstOrFail(),
        ]);
    }
}
