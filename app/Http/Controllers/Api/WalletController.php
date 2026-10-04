<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;

/**
 * Customer wallet: current balance + transaction history.
 * The wallet is a ledger — balance is the latest running balance_after.
 */
class WalletController extends Controller
{
    use ApiReturnFormatTrait;

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user instanceof Customer) {
            return $this->responseWithError('Only customers have a wallet here.', [], 403);
        }

        $transactions = WalletTransaction::where('customer_id', $user->id)
            ->orderByDesc('id')
            ->get();

        // Balance = newest transaction's running balance_after (ledger truth,
        // and the same rule CustomerWalletService rebuilds it with).
        $balance = app(\App\Services\Accounting\CustomerWalletService::class)
            ->balance((int) $user->id);

        return $this->responseWithSuccess('Wallet fetched.', [
            'balance'      => $balance,
            'currency'     => 'BDT',
            'transactions' => $transactions->map(fn (WalletTransaction $t) => [
                'id'            => $t->id,
                'reference'     => $t->reference,
                'type'          => $t->type,
                'amount'        => (float) $t->amount,
                'description'   => $t->description,
                'txn_date'      => optional($t->txn_date)->toDateString(),
                'balance_after' => (float) $t->balance_after,
            ]),
        ]);
    }

    // The former topup() action credited the wallet directly from a customer
    // request with no gateway behind it — real money never moved, so it was
    // a mint-your-own-balance hole. Removed rather than wired to a gateway:
    // a customer wallet here is funded only by a receipt/refund posting to it
    // (BillingService) or by staff recording cash received in person
    // (CustomerController::walletAdjust), both of which book real money.
}
