<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use App\Traits\ResolvesCustomer;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ApiTransformTrait;
use Illuminate\Http\Request;

/**
 * Customer home summary for the app — the counters the web portal shows on
 * /portal/customer/dashboard, so both surfaces report the same numbers.
 */
class DashboardController extends Controller
{
    use ApiReturnFormatTrait, ApiTransformTrait, ResolvesCustomer;

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        if (! $customer) {
            return $this->responseWithError('Only customers have a dashboard here.', [], 403);
        }

        $recent = Booking::with('package')
            ->where('customer_id', $customer->id)
            ->latest()
            ->take(8)
            ->get();

        return $this->responseWithSuccess('Dashboard loaded.', [
            'summary' => [
                'booking_count'  => $customer->bookings()->count(),
                'upcoming_count' => $customer->bookings()
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
                'wallet_balance' => $this->walletBalance($customer),
                'visa_count'     => $customer->visaApplications()->count(),
                // Own rows plus general broadcasts — the same set /notifications
                // lists, so the badge can never outrun the list.
                'unread_notifications' => Notification::query()
                    ->where(function ($q) use ($customer) {
                        $q->where(function ($q2) use ($customer) {
                            $q2->where('notifiable_type', Customer::class)
                               ->where('notifiable_id', $customer->id);
                        })->orWhere(function ($q2) {
                            $q2->whereNull('notifiable_type')->whereNull('user_id');
                        });
                    })
                    ->where('is_read', false)
                    ->count(),
            ],
            'recent_bookings' => $recent->map(fn (Booking $b) => $this->bookingInfo($b)),
        ]);
    }

    /**
     * Balance is the newest transaction's running `balance_after` — the same
     * ledger truth /wallet reports. The web portal sums credits minus debits
     * instead; that drifts from the ledger if a row is ever back-dated, and the
     * app must not show the wallet card and the wallet screen disagreeing.
     */
    private function walletBalance(Customer $customer): float
    {
        $latest = WalletTransaction::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->first();

        return $latest ? (float) $latest->balance_after : 0.0;
    }
}
