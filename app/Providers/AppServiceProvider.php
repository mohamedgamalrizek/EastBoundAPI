<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\Mail\GmailApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\AccountTransaction;
use App\Models\AgentCommission;
use App\Models\AgentInvoice;
use App\Models\AgentWithdrawal;
use App\Models\Booking;
use App\Models\EventBooking;
use App\Models\FlightBooking;
use App\Models\HajjPilgrim;
use App\Models\HotelBooking;
use App\Models\Invoice;
use App\Models\Payslip;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\SupplierTransaction;
use App\Models\TransportBooking;
use App\Models\WalletTransaction;
use App\Observers\AccountTransactionObserver;
use App\Observers\AgentCommissionObserver;
use App\Observers\AgentInvoiceObserver;
use App\Observers\AgentWithdrawalObserver;
use App\Observers\BookingObserver;
use App\Observers\EventBookingObserver;
use App\Observers\FlightBookingObserver;
use App\Observers\HajjPilgrimObserver;
use App\Observers\HotelBookingObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PayslipObserver;
use App\Observers\ReceiptObserver;
use App\Observers\RefundObserver;
use App\Observers\SupplierTransactionObserver;
use App\Observers\TransportBookingObserver;
use App\Observers\WalletTransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No Fortify response bindings needed for Blade

        // One registry of online payment gateways for the whole app. Built
        // once: each gateway reads its credentials from the cached settings.
        $this->app->singleton(\App\Services\Payments\PaymentManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Gmail API mail transport. Registered unconditionally — it is inert
        // until Settings -> Mail selects it, and registering it lazily would
        // mean the driver is missing on the very first request after an admin
        // switches to it.
        Mail::extend('gmail_api', fn (array $config = []) => new GmailApiTransport());

        // Rate limiter for the mobile API middleware group (routes/api.php).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Unauthenticated API auth endpoints — login, register, OTP and password
        // reset. Without a limiter these are a free brute-force surface: a
        // password or a six-digit OTP can be walked at request speed. Counted
        // per credential AND per IP, so one attacker cannot hide behind many
        // accounts, and one shared office IP cannot lock out its own users.
        RateLimiter::for('api-auth', function (Request $request) {
            $identifier = (string) ($request->input('email')
                ?: $request->input('phone')
                ?: $request->input('username')
                ?: '');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by(mb_strtolower($identifier) . '|' . $request->ip()),
            ];
        });

        // OTP and reset-token verification is stricter still: the code is only
        // six digits, so the guess space is small enough to matter.
        RateLimiter::for('api-otp', function (Request $request) {
            $identifier = (string) ($request->input('email') ?: $request->input('phone') ?: '');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinutes(10, 10)->by(mb_strtolower($identifier) . '|' . $request->ip()),
            ];
        });

        // Accounting: business documents post themselves to the journal, and
        // the journal keeps account balances derived. Registered as observers
        // so this holds for every write path — admin CRUD, API and seeders.
        Invoice::observe(InvoiceObserver::class);
        Receipt::observe(ReceiptObserver::class);
        Payslip::observe(PayslipObserver::class);
        Refund::observe(RefundObserver::class);
        SupplierTransaction::observe(SupplierTransactionObserver::class);
        WalletTransaction::observe(WalletTransactionObserver::class);
        AccountTransaction::observe(AccountTransactionObserver::class);

        // Agent settlement: a paid booking earns its agent a commission, an
        // approved commission lands in their wallet, and a paid withdrawal
        // takes it out again — each step posting itself to the books.
        Booking::observe(BookingObserver::class);
        AgentCommission::observe(AgentCommissionObserver::class);
        AgentWithdrawal::observe(AgentWithdrawalObserver::class);
        AgentInvoice::observe(AgentInvoiceObserver::class);

        // Service bookings bill themselves the same way tour bookings do:
        // confirm -> invoice, pay -> receipt, cancel -> unpaid invoice gone.
        // Flights invoice on ticketing (ticket number + fare) — the one line
        // that never posted to the books before the observer existed.
        HotelBooking::observe(HotelBookingObserver::class);
        TransportBooking::observe(TransportBookingObserver::class);
        FlightBooking::observe(FlightBookingObserver::class);
        EventBooking::observe(EventBookingObserver::class);
        HajjPilgrim::observe(HajjPilgrimObserver::class);
    }
}
