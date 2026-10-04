<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\VisaController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TravelerController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransportController;
use App\Http\Controllers\Api\HajjController;
use App\Http\Controllers\Api\PassportController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentPdfController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\GrowthFeatureController;

/*
|--------------------------------------------------------------------------
| FLOW Mobile App API (v1)
|--------------------------------------------------------------------------
| Consumed by the FLOW mobile app (single app, role-based: customer +
| agent). All responses use the {success, message, data} envelope from
| ApiReturnFormatTrait. Protected routes use Sanctum tokens.
|
| NOTE: When SaaS multi-tenant mode is enabled, a tenant-resolution middleware
| (by subdomain or X-Tenant header) will be added to this group so each
| request hits the right agency. For the central (single-agency) app it works
| as-is.
*/

Route::prefix('v1')->group(function () {

    // ---- Public auth ----
    // Throttled: these are the only unauthenticated endpoints that accept a
    // credential, so they are the brute-force surface. `api-auth` and `api-otp`
    // are defined in AppServiceProvider and count per credential and per IP.
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:api-auth');
        Route::post('login',    [AuthController::class, 'login'])->middleware('throttle:api-auth');
        Route::post('otp/request', [AuthController::class, 'requestOtp'])->middleware('throttle:api-auth');
        Route::post('otp/verify',  [AuthController::class, 'verifyOtp'])->middleware('throttle:api-otp');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:api-otp');

        // ---- Mobile social sign-in (native SDK + token exchange) ----
        // Same brute-force surface as password login, same throttle. The
        // providers endpoint is secret-free (enabled flags only).
        Route::post('social/google',   [SocialAuthController::class, 'google'])->middleware('throttle:api-auth');
        Route::post('social/facebook', [SocialAuthController::class, 'facebook'])->middleware('throttle:api-auth');
        Route::get('social/providers', [SocialAuthController::class, 'providers']);
    });

    // ---- Public browsing (Home + Tours + Hotels) ----
    Route::get('home', [HomeController::class, 'index']);
    Route::get('tours', [TourController::class, 'index']);
    Route::get('tours/{id}', [TourController::class, 'show']);
    Route::get('hotels', [HotelController::class, 'index']);
    Route::get('hotels/{id}', [HotelController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);

    // ---- Public browsing (Hajj & Umrah packages + transport types) ----
    Route::get('hajj/packages',      [HajjController::class, 'packages']);
    Route::get('hajj/packages/{id}', [HajjController::class, 'showPackage']);
    Route::get('transport/types',    [TransportController::class, 'types']);
    Route::get('transport/vehicle-categories', [TransportController::class, 'vehicleCategories']);
    Route::get('transport-services', [TransportController::class, 'services']);
    Route::get('flight-routes',      [FlightController::class, 'routes']);
    Route::get('live-flights', [GrowthFeatureController::class, 'flights'])->middleware('throttle:20,1');

    // ---- Traveller reviews (public read; writing one needs a paid booking) ----
    Route::get('packages/{id}/reviews', [ReviewController::class, 'index']);

    // ---- Visa catalogue + reference tracking (public, same as the website) ----
    Route::get('visa-services', [VisaController::class, 'services']);
    Route::get('visa/track',    [VisaController::class, 'track']);

    // ---- Public content (help + marketing + agent signup) ----
    Route::get('faqs',            [ContentController::class, 'faqs']);
    Route::get('content-blocks',  [ContentController::class, 'contentBlocks']);
    Route::get('testimonials',    [ContentController::class, 'testimonials']);
    Route::post('become-an-agent', [ContentController::class, 'becomeAgent']);
    Route::get('enquiry-types',   [ContentController::class, 'enquiryTypes']);
    Route::post('enquiries',      [ContentController::class, 'enquiry']);
    Route::get('contact-info',    [ContentController::class, 'contactInfo']);
    Route::post('contact',        [ContentController::class, 'contact']);

    // ---- App branding (splash logo, app name, currency) from admin settings ----
    Route::get('settings', [SettingsController::class, 'index']);

    // ---- App localisation (admin's App Language module + mobile phrases) ----
    Route::get('app-languages',       [\App\Http\Controllers\Api\AppLanguageController::class, 'languages']);
    Route::get('app-language/terms',  [\App\Http\Controllers\Api\AppLanguageController::class, 'terms']);

    // ---- Authenticated (Sanctum) ----
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('me',      [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });

        // ---- Home summary (customer) — mirrors the web portal dashboard ----
        Route::get('dashboard', [DashboardController::class, 'index']);

        // ---- Bookings (customer) ----
        Route::get('bookings',      [BookingController::class, 'index']);
        Route::post('bookings',     [BookingController::class, 'store']);
        // Price a trip (promo code / points) before committing to it.
        Route::post('bookings/quote', [BookingController::class, 'quote'])->middleware('throttle:30,1');
        Route::get('bookings/{id}', [BookingController::class, 'show']);
        Route::post('bookings/{id}/pay',    [PaymentController::class, 'pay']);
        Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);

        // ---- Hotel bookings (customer) ----
        Route::post('hotel-bookings', [HotelController::class, 'book']);
        Route::get('hotel-bookings',  [HotelController::class, 'myBookings']);
        Route::post('hotel-bookings/{id}/pay', [HotelController::class, 'pay']);

        // ---- Flight bookings (customer, request-to-book) ----
        Route::get('flights',  [FlightController::class, 'index']);
        Route::get('trip-plans', [GrowthFeatureController::class, 'plans']);
        Route::post('trip-plans', [GrowthFeatureController::class, 'generate'])->middleware('throttle:10,1');
        Route::get('loyalty', [GrowthFeatureController::class, 'loyalty']);
        Route::post('flights', [FlightController::class, 'store']);

        // ---- Transport bookings (customer, request-to-book) ----
        Route::get('transport',              [TransportController::class, 'index']);
        Route::post('transport',             [TransportController::class, 'store']);
        // Drivers the agent's transport form offers (staff data — authenticated).
        Route::get('transport/drivers',      [TransportController::class, 'drivers']);
        Route::get('transport/{id}',         [TransportController::class, 'show']);
        Route::post('transport/{id}/pay',    [TransportController::class, 'pay']);
        Route::post('transport/{id}/cancel', [TransportController::class, 'cancel']);

        // ---- Passports (customer) ----
        Route::get('passports',           [PassportController::class, 'index']);
        Route::post('passports',          [PassportController::class, 'store']);
        Route::put('passports/{id}',      [PassportController::class, 'update']);
        Route::delete('passports/{id}',   [PassportController::class, 'destroy']);

        // ---- Documents held by the agency (customer, read-only) ----
        Route::get('documents', [DocumentController::class, 'index']);

        // ---- Billing: invoices + receipts (customer, read-only) ----
        Route::get('invoices',      [InvoiceController::class, 'index']);
        Route::get('invoices/{id}', [InvoiceController::class, 'show']);
        Route::get('payments',      [InvoiceController::class, 'payments']);

        // ---- Printable documents (PDF, scoped to the signed-in customer) ----
        Route::get('invoices/{id}/pdf',       [DocumentPdfController::class, 'invoice']);
        Route::get('receipts/{id}/pdf',       [DocumentPdfController::class, 'receipt']);
        Route::get('hotel-bookings/{id}/voucher', [DocumentPdfController::class, 'hotelVoucher']);
        Route::get('flights/{id}/eticket',    [DocumentPdfController::class, 'eTicket']);

        // ---- Hajj & Umrah registrations (customer) ----
        Route::get('hajj/registrations', [HajjController::class, 'registrations']);
        Route::post('hajj/register',     [HajjController::class, 'register']);

        // ---- Wallet (customer) ----
        // No topup route: a customer wallet can only be funded by a real
        // receipt/refund posting (BillingService) or a staff-recorded cash
        // top-up (CustomerController::walletAdjust) — never by the customer
        // crediting themselves. See WalletController for the removed action.
        Route::get('wallet', [WalletController::class, 'index']);

        // ---- Visa (customer) ----
        Route::get('visa',      [VisaController::class, 'index']);
        Route::post('visa',     [VisaController::class, 'apply']);
        Route::get('visa/{id}', [VisaController::class, 'show']);
        Route::get('visa/documents/{id}/download', [VisaController::class, 'downloadDocument']);

        // ---- Profile (customer) ----
        Route::get('profile',          [ProfileController::class, 'show']);
        Route::put('profile',          [ProfileController::class, 'update']);

        // ---- Account preferences (customer or agent/staff user) ----
        Route::get('preferences', [\App\Http\Controllers\Api\PreferencesController::class, 'show']);
        Route::put('preferences', [\App\Http\Controllers\Api\PreferencesController::class, 'update']);
        Route::post('profile/avatar',  [ProfileController::class, 'updateAvatar']);
        Route::post('profile/password', [ProfileController::class, 'changePassword']);

        // ---- Saved travelers (customer) ----
        Route::get('travelers',         [TravelerController::class, 'index']);
        Route::post('travelers',        [TravelerController::class, 'store']);
        Route::put('travelers/{id}',    [TravelerController::class, 'update']);
        Route::delete('travelers/{id}', [TravelerController::class, 'destroy']);

        // ---- Notifications (any account) ----
        Route::get('notifications',              [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read',   [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all',    [NotificationController::class, 'markAllRead']);

        // ---- Push (FCM) device tokens (any account) ----
        Route::post('device-tokens',   [\App\Http\Controllers\Api\DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [\App\Http\Controllers\Api\DeviceTokenController::class, 'destroy']);

        // ---- Support tickets (customer) ----
        Route::get('support/tickets',  [SupportController::class, 'index']);
        Route::post('support/tickets', [SupportController::class, 'store']);

        // ---- Guide rating (customer, after the tour) ----
        Route::post('bookings/{id}/guide-rating', [BookingController::class, 'rateGuide']);

        // ---- Reviews the customer writes on their own trips ----
        Route::get('reviews',                 [ReviewController::class, 'mine']);
        Route::post('bookings/{id}/review',   [ReviewController::class, 'store']);

        // ---- Guide (tour guide role) ----
        Route::prefix('guide')->group(function () {
            Route::get('assignments', [\App\Http\Controllers\Api\GuideController::class, 'assignments']);
            Route::post('assignments/{id}/start', [\App\Http\Controllers\Api\GuideController::class, 'start']);
            Route::post('assignments/{id}/complete', [\App\Http\Controllers\Api\GuideController::class, 'complete']);
        });

        // ---- Agent (B2B) ----
        Route::prefix('agent')->group(function () {
            Route::get('dashboard',   [AgentController::class, 'dashboard']);
            Route::get('wallet',      [AgentController::class, 'wallet']);
            Route::post('wallet/withdraw', [AgentController::class, 'requestWithdrawal']);
            Route::get('commissions', [AgentController::class, 'commissions']);
            Route::get('bookings',    [AgentController::class, 'bookings']);
            Route::post('bookings',   [AgentController::class, 'storeBooking']);
            // Hotel stays sold to clients (same action the portal form uses).
            Route::post('hotel-bookings', [AgentController::class, 'storeHotelBooking']);
            // Transport trips sold to clients (same action the portal form uses).
            Route::post('transport-bookings', [AgentController::class, 'storeTransportBooking']);
            // Flight requests sold to clients (same action the portal form uses).
            Route::post('flight-bookings', [AgentController::class, 'storeFlightBooking']);
            // Open one, amend it or drop it while the desk has not priced it.
            Route::get('bookings/{id}',        [AgentController::class, 'showBooking']);
            Route::put('bookings/{id}',        [AgentController::class, 'updateBooking']);
            Route::post('bookings/{id}/cancel', [AgentController::class, 'cancelBooking']);
            Route::get('customers',   [AgentController::class, 'customers']);
            Route::get('invoices',     [AgentController::class, 'invoices']);
            Route::get('transactions', [AgentController::class, 'transactions']);
            Route::get('reports',      [AgentController::class, 'reports']);
            Route::get('support',      [AgentController::class, 'support']);
        });
    });
});

// Default Laravel helper (kept for compatibility).
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
