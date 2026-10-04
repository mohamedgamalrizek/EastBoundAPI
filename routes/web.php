<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Backend\AccountTransactionController;
use App\Http\Controllers\Backend\AccountingController;
use App\Http\Controllers\Backend\ActivityLogController;
use App\Http\Controllers\Backend\AgentCommissionController;
use App\Http\Controllers\Backend\AgentController;
use App\Http\Controllers\Backend\AgentInvoiceController;
use App\Http\Controllers\Backend\AgentPortalController;
use App\Http\Controllers\Backend\AgentWithdrawalController;
use App\Http\Controllers\Backend\AnnouncementController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\BranchController;
use App\Http\Controllers\Backend\CampaignController;
use App\Http\Controllers\Backend\CmsController;
use App\Http\Controllers\Backend\ContactMessageController;
use App\Http\Controllers\Backend\ContentBlockController;
use App\Http\Controllers\Backend\CorporateTravelController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Backend\CrmActivityController;
use App\Http\Controllers\Backend\CrmController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\CustomerPortalController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DocumentController;
use App\Http\Controllers\Backend\DriverController;
use App\Http\Controllers\Backend\EventBookingController;
use App\Http\Controllers\Backend\EventTourController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\FlightController;
use App\Http\Controllers\Backend\FlightRouteController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\HajjController;
use App\Http\Controllers\Backend\HajjPilgrimController;
use App\Http\Controllers\Backend\HotelBookingController;
use App\Http\Controllers\Backend\HotelController;
use App\Http\Controllers\Backend\HotelRoomController;
use App\Http\Controllers\Backend\InsuranceController;
use App\Http\Controllers\Backend\InvoiceController;
use App\Http\Controllers\Backend\JobApplicationController;
use App\Http\Controllers\Backend\JobOpeningController;
use App\Http\Controllers\Backend\KbArticleController;
use App\Http\Controllers\Backend\LanguageController;
use App\Http\Controllers\Backend\LeadController;
use App\Http\Controllers\Backend\LeaveRequestController;
use App\Http\Controllers\Backend\LoginActivityController;
use App\Http\Controllers\Backend\MedicalTourController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\NewsletterSubscriberController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\PackageController;
use App\Http\Controllers\Backend\PassportController;
use App\Http\Controllers\Backend\PayslipController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\ReceiptController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\SocialLinkController;
use App\Http\Controllers\Backend\StaffAttendanceController;
use App\Http\Controllers\Backend\StaffPortalController;
use App\Http\Controllers\Backend\StudentServiceController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\SupportController;
use App\Http\Controllers\Backend\TaskController;
use App\Http\Controllers\Backend\TestimonialController;
use App\Http\Controllers\Backend\TodoController;
use App\Http\Controllers\Backend\TourController;
use App\Http\Controllers\Backend\TourScheduleController;
use App\Http\Controllers\Backend\TransportController;
use App\Http\Controllers\Backend\TransportServiceController;
use App\Http\Controllers\Backend\TravelerController;
use App\Http\Controllers\Backend\VehicleCategoryController;
use App\Http\Controllers\Backend\VisaController;
use App\Http\Controllers\Backend\VisaServiceController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\GrowthFeatureController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All "web" routes for your application live here.
| These routes are loaded automatically by Laravel 12 from bootstrap/app.php.
|
*/

// 🔹 Cache clear route (no closure, safe for route:cache)
Route::get('cache-clear', function () {
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', ___('alert.cache_successfully_cleared.'));
})->name('cache.clear')->middleware('auth', 'hasPermission:dashboard_read');

// 🔹 Public frontend website (no auth)
Route::redirect('/', '/login')->name('home');
//Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('tour-packages', [FrontendController::class, 'packages'])->name('front.packages');
Route::get('tour-packages/{id}', [FrontendController::class, 'packageShow'])->name('front.package');
Route::post('tour-packages/{id}/book', [FrontendController::class, 'bookStore'])->name('front.book.store');
Route::get('visa-services', [FrontendController::class, 'visa'])->name('front.visa');
Route::get('about-us', [FrontendController::class, 'about'])->name('front.about');
Route::get('contact-us', [FrontendController::class, 'contact'])->name('front.contact');
Route::post('contact-us', [FrontendController::class, 'contactStore'])->name('front.contact.store');

// 🔹 Payment provider callback (public: the provider posts here from its own
// servers, so there is no session and no CSRF token — see VerifyCsrfToken).
// Both verbs: browsers come back by GET, IPNs arrive as POST.
Route::match(['get', 'post'], 'payment/callback/{gateway}/{reference}',
    [PaymentCallbackController::class, 'handle'])->name('payment.callback');

// 🔹 Public — service landing pages (all CMS/DB driven)
Route::get('hajj', [FrontendController::class, 'hajj'])->name('front.hajj');
Route::get('umrah', [FrontendController::class, 'umrah'])->name('front.umrah');
Route::get('flight-booking', [FrontendController::class, 'flights'])->name('front.flights');
Route::get('flight-booking/live-search', [GrowthFeatureController::class, 'flights'])->middleware('throttle:20,1')->name('front.flights.live');
Route::get('hotel-booking', [FrontendController::class, 'hotels'])->name('front.hotels');
Route::get('transport-booking', [FrontendController::class, 'transport'])->name('front.transport');

// 🔹 Public — content & info pages (CMS-managed)
Route::get('gallery', [FrontendController::class, 'gallery'])->name('front.gallery');
Route::get('faq', [FrontendController::class, 'faq'])->name('front.faq');
Route::get('testimonials', [FrontendController::class, 'testimonials'])->name('front.testimonials');
Route::get('privacy-policy', [FrontendController::class, 'privacy'])->name('front.privacy');
Route::get('terms-conditions', [FrontendController::class, 'terms'])->name('front.terms');
Route::get('refund-policy', [FrontendController::class, 'refund'])->name('front.refund');
Route::get('cancellation-policy', [FrontendController::class, 'cancellation'])->name('front.cancellation');
Route::get('career', [FrontendController::class, 'career'])->name('front.career');
Route::get('career/{job}/apply', [FrontendController::class, 'careerApply'])->name('front.career.apply');
Route::post('career/{job}/apply', [FrontendController::class, 'careerApplyStore'])->name('front.career.apply.store');
Route::get('support-center', [FrontendController::class, 'support'])->name('front.support');

// 🔹 Public — newsletter sign-up
Route::post('newsletter/subscribe', [FrontendController::class, 'newsletterStore'])->name('front.newsletter');

// 🔹 Public — blog
Route::get('blog', [FrontendController::class, 'blog'])->name('front.blog');
Route::get('blog/{slug}', [FrontendController::class, 'blogShow'])->name('front.blog.show');

// 🔹 Public — become an agent (creates a CRM Lead)
Route::get('become-an-agent', [FrontendController::class, 'becomeAgent'])->name('front.become.agent');
Route::post('become-an-agent', [FrontendController::class, 'becomeAgentStore'])->name('front.become.agent.store');

// 🔹 Public — tracking
Route::get('track-booking', [FrontendController::class, 'trackBooking'])->name('front.track.booking');
Route::get('track-visa', [FrontendController::class, 'trackVisa'])->name('front.track.visa');

// 🔹 Public — booking forms (one view, type-driven; each request creates a CRM Lead)
Route::get('book/{type}', [FrontendController::class, 'bookingForm'])->name('front.book');
Route::post('book/{type}', [FrontendController::class, 'bookingStore'])->name('front.book.request');

// NOTE: the old themed frontend demo portals (my-account/*, agent-portal/*) were
// removed — Customer & Agent now log in and use the real backend portals
// (/portal/customer/*, /portal/agent/*).

// 🔹 Wishlist — the heart icon on public package cards (signed-in customers only)
Route::post('wishlist/{package}/toggle', [WishlistController::class, 'toggle'])->name('front.wishlist.toggle')->middleware('auth');

// 🔹 Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('hasPermission:dashboard_read');

    // Profile
    Route::get('profile', [ProfileController::class, 'profile'])->name('profile')->middleware('hasPermission:profile_read');
    Route::get('profile/update', [ProfileController::class, 'profileEdit'])->name('profile.edit')->middleware('hasPermission:profile_update');
    Route::put('profile/update', [ProfileController::class, 'profileUpdate'])->name('profile.update')->middleware('hasPermission:profile_update');
    Route::get('password/update', [ProfileController::class, 'passwordEdit'])->name('password.edit')->middleware('hasPermission:password_update');
    // Named `profile.password.update`, not `password.update`: Fortify already
    // registers that name for its reset-password route, and the collision made
    // `php artisan route:cache` fail outright.
    Route::put('password/update', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update')->middleware('hasPermission:password_update');

    // Notifications — the bell, shared by every panel. No `hasPermission:` gate,
    // same as profile/password above: any authenticated account can reach it,
    // scoped to their own rows inside the controller.
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // To-do
    Route::get('todo/todo_list', [TodoController::class, 'index'])->name('todo.index')->middleware('hasPermission:todo_read');
    Route::get('todo/todo_create', [TodoController::class, 'create'])->name('todo.create')->middleware('hasPermission:todo_create');
    Route::post('todo/todo_add', [TodoController::class, 'store'])->name('todo.store')->middleware('hasPermission:todo_create');
    Route::get('todo/edit/{id}', [TodoController::class, 'edit'])->name('todo.edit')->middleware('hasPermission:todo_update');
    Route::put('todo/update', [TodoController::class, 'update'])->name('todo.update')->middleware('hasPermission:todo_update');
    Route::delete('todo/delete/{id}', [TodoController::class, 'delete'])->name('todo.delete')->middleware('hasPermission:todo_delete');

    // Tour Packages (minimal CRUD — auth only, add hasPermission later if needed)
    Route::get('packages', [PackageController::class, 'index'])->name('package.index')->middleware('hasPermission:package_read');
    Route::get('packages/create', [PackageController::class, 'create'])->name('package.create')->middleware('hasPermission:package_create');
    Route::post('packages', [PackageController::class, 'store'])->name('package.store')->middleware('hasPermission:package_create');
    Route::get('packages/{id}/edit', [PackageController::class, 'edit'])->name('package.edit')->middleware('hasPermission:package_update');
    Route::put('packages/{id}', [PackageController::class, 'update'])->name('package.update')->middleware('hasPermission:package_update');
    Route::delete('packages/{id}', [PackageController::class, 'delete'])->name('package.delete')->middleware('hasPermission:package_delete');

    // Bookings (minimal CRUD — auth only)
    Route::get('bookings', [BookingController::class, 'index'])->name('booking.index')->middleware('hasPermission:booking_read');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('booking.create')->middleware('hasPermission:booking_create');
    Route::post('bookings', [BookingController::class, 'store'])->name('booking.store')->middleware('hasPermission:booking_create');
    Route::get('bookings/{id}/edit', [BookingController::class, 'edit'])->name('booking.edit')->middleware('hasPermission:booking_update');
    Route::put('bookings/{id}', [BookingController::class, 'update'])->name('booking.update')->middleware('hasPermission:booking_update');
    Route::delete('bookings/{id}', [BookingController::class, 'delete'])->name('booking.delete')->middleware('hasPermission:booking_delete');

    // Customers (minimal CRUD — auth only)
    Route::get('customers', [CustomerController::class, 'index'])->name('customer.index')->middleware('hasPermission:customer_read');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customer.create')->middleware('hasPermission:customer_create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customer.store')->middleware('hasPermission:customer_create');
    Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customer.show')->middleware('hasPermission:customer_read');
    Route::get('customers/{id}/edit', [CustomerController::class, 'edit'])->name('customer.edit')->middleware('hasPermission:customer_update');
    Route::put('customers/{id}', [CustomerController::class, 'update'])->name('customer.update')->middleware('hasPermission:customer_update');
    Route::delete('customers/{id}', [CustomerController::class, 'delete'])->name('customer.delete')->middleware('hasPermission:customer_delete');
    Route::post('customers/{id}/wallet', [CustomerController::class, 'walletAdjust'])->name('customer.wallet.adjust')->middleware('hasPermission:customer_update');

    /*
    |--------------------------------------------------------------------------
    | Dummy UI pages (static views, inline dummy data) — for UI/mastering
    | Route::view() is route:cache-safe (no closures).
    |--------------------------------------------------------------------------
    */
    // CRM — Leads (real CRUD)
    Route::get('crm/leads', [LeadController::class, 'index'])->name('crm.leads')->middleware('hasPermission:crm_read');
    Route::get('crm/leads/create', [LeadController::class, 'create'])->name('crm.leads.create')->middleware('hasPermission:crm_create');
    Route::post('crm/leads', [LeadController::class, 'store'])->name('crm.leads.store')->middleware('hasPermission:crm_create');
    Route::get('crm/leads/{id}', [LeadController::class, 'show'])->name('crm.leads.show')->middleware('hasPermission:crm_read');
    Route::get('crm/leads/{id}/edit', [LeadController::class, 'edit'])->name('crm.leads.edit')->middleware('hasPermission:crm_update');
    Route::put('crm/leads/{id}', [LeadController::class, 'update'])->name('crm.leads.update')->middleware('hasPermission:crm_update');
    Route::delete('crm/leads/{id}', [LeadController::class, 'delete'])->name('crm.leads.delete')->middleware('hasPermission:crm_delete');
    // Convert a flight lead into a real FlightBooking (pre-filled from the lead).
    Route::get('crm/leads/{id}/convert-flight', [LeadController::class, 'convertFlight'])->name('crm.leads.convert.flight')->middleware('hasPermission:crm_update');
    Route::post('crm/leads/{id}/convert-flight', [LeadController::class, 'storeFlightBooking'])->name('crm.leads.store.flight')->middleware('hasPermission:crm_update');
    // Convert a hotel lead into a real HotelBooking (pre-filled from the lead).
    Route::get('crm/leads/{id}/convert-hotel', [LeadController::class, 'convertHotel'])->name('crm.leads.convert.hotel')->middleware('hasPermission:crm_update');
    Route::post('crm/leads/{id}/convert-hotel', [LeadController::class, 'storeHotelBooking'])->name('crm.leads.store.hotel')->middleware('hasPermission:crm_update');
    Route::get('crm/contact-messages', [ContactMessageController::class, 'index'])->name('crm.contact-messages.index')->middleware('hasPermission:crm_read');
    Route::get('crm/contact-messages/{id}', [ContactMessageController::class, 'show'])->name('crm.contact-messages.show')->middleware('hasPermission:crm_read');
    Route::delete('crm/contact-messages/{id}/delete', [ContactMessageController::class, 'delete'])->name('crm.contact-messages.delete')->middleware('hasPermission:crm_delete');
    Route::get('crm/job-applications', [JobApplicationController::class, 'index'])->name('crm.job-applications.index')->middleware('hasPermission:crm_read');
    Route::get('crm/job-applications/{id}', [JobApplicationController::class, 'show'])->name('crm.job-applications.show')->middleware('hasPermission:crm_read');
    Route::get('crm/job-applications/{id}/resume', [JobApplicationController::class, 'resume'])->name('crm.job-applications.resume')->middleware('hasPermission:crm_read');
    Route::delete('crm/job-applications/{id}/delete', [JobApplicationController::class, 'delete'])->name('crm.job-applications.delete')->middleware('hasPermission:crm_delete');
    // CRM — other (dummy)
    Route::get('crm/follow-up-calendar', [CrmController::class, 'followup'])->name('crm.followup')->middleware('hasPermission:crm_read');
    Route::get('crm/activity-timeline', [CrmController::class, 'activity'])->name('crm.activity')->middleware('hasPermission:crm_read');
    Route::get('crm/notes', [CrmController::class, 'notes'])->name('crm.notes')->middleware('hasPermission:crm_read');
    Route::get('crm/communication-history', [CrmController::class, 'communication'])->name('crm.communication')->middleware('hasPermission:crm_read');
    // CRM Activities CRUD (sub-entity, reuses crm_* permissions)
    Route::get('crm/activities/manage', [CrmActivityController::class, 'index'])->name('crm.activity.index')->middleware('hasPermission:crm_read');
    Route::get('crm/activities/manage/create', [CrmActivityController::class, 'create'])->name('crm.activity.create')->middleware('hasPermission:crm_create');
    Route::post('crm/activities/manage/store', [CrmActivityController::class, 'store'])->name('crm.activity.store')->middleware('hasPermission:crm_create');
    Route::get('crm/activities/manage/{id}/edit', [CrmActivityController::class, 'edit'])->name('crm.activity.edit')->middleware('hasPermission:crm_update');
    Route::put('crm/activities/manage/update', [CrmActivityController::class, 'update'])->name('crm.activity.update')->middleware('hasPermission:crm_update');
    Route::delete('crm/activities/manage/{id}/delete', [CrmActivityController::class, 'delete'])->name('crm.activity.delete')->middleware('hasPermission:crm_delete');

    // Visa Management
    Route::get('visa/dashboard', [VisaController::class, 'dashboard'])->name('visa.dashboard')->middleware('hasPermission:visa_read');
    Route::get('visa/applications', [VisaController::class, 'applications'])->name('visa.applications')->middleware('hasPermission:visa_read');
    // Visa application CRUD (literal segments declared before the {id} show route)
    Route::get('visa/applications/create', [VisaController::class, 'create'])->name('visa.application.create')->middleware('hasPermission:visa_create');
    Route::post('visa/applications/store', [VisaController::class, 'store'])->name('visa.application.store')->middleware('hasPermission:visa_create');
    Route::get('visa/applications/{id}/edit', [VisaController::class, 'edit'])->name('visa.application.edit')->middleware('hasPermission:visa_update');
    Route::put('visa/applications/update', [VisaController::class, 'update'])->name('visa.application.update')->middleware('hasPermission:visa_update');
    Route::delete('visa/applications/{id}/delete', [VisaController::class, 'delete'])->name('visa.application.delete')->middleware('hasPermission:visa_delete');
    Route::get('visa/applications/{id}', [VisaController::class, 'applicationDetails'])->name('visa.application.details')->middleware('hasPermission:visa_read');
    Route::get('visa/embassy-appointment', [VisaController::class, 'appointment'])->name('visa.appointment')->middleware('hasPermission:visa_read');
    Route::get('visa/embassy-appointment/create', [VisaController::class, 'appointmentCreate'])->name('visa.appointment.create')->middleware('hasPermission:visa_update');
    Route::post('visa/embassy-appointment', [VisaController::class, 'bookAppointment'])->name('visa.appointment.store')->middleware('hasPermission:visa_update');
    Route::put('visa/embassy-appointment', [VisaController::class, 'updateAppointment'])->name('visa.appointment.update')->middleware('hasPermission:visa_update');
    Route::get('visa/embassy-appointment/{id}/edit', [VisaController::class, 'appointmentEdit'])->name('visa.appointment.edit')->middleware('hasPermission:visa_update');
    Route::delete('visa/embassy-appointment/{id}', [VisaController::class, 'deleteAppointment'])->name('visa.appointment.delete')->middleware('hasPermission:visa_delete');
    Route::get('visa/documents', [VisaController::class, 'documents'])->name('visa.documents')->middleware('hasPermission:visa_read');
    Route::get('visa/documents/create', [VisaController::class, 'documentCreate'])->name('visa.documents.create')->middleware('hasPermission:visa_update');
    Route::post('visa/documents', [VisaController::class, 'uploadDocument'])->name('visa.documents.store')->middleware('hasPermission:visa_update');
    Route::put('visa/documents', [VisaController::class, 'updateDocument'])->name('visa.documents.update')->middleware('hasPermission:visa_update');
    Route::get('visa/documents/{id}/edit', [VisaController::class, 'documentEdit'])->name('visa.documents.edit')->middleware('hasPermission:visa_update');
    Route::delete('visa/documents/{id}', [VisaController::class, 'deleteDocument'])->name('visa.documents.delete')->middleware('hasPermission:visa_delete');
    Route::get('visa/documents/{id}/download', [VisaController::class, 'downloadDocument'])->name('visa.documents.download')->middleware('hasPermission:visa_read');
    Route::get('visa/status-tracking', [VisaController::class, 'tracking'])->name('visa.tracking')->middleware('hasPermission:visa_read');
    Route::put('visa/status-tracking/{id}', [VisaController::class, 'advance'])->name('visa.advance')->middleware('hasPermission:visa_update');
    Route::get('visa/expiry-management', [VisaController::class, 'expiry'])->name('visa.expiry')->middleware('hasPermission:visa_read');
    Route::post('visa/expiry-management/{id}/notify', [VisaController::class, 'notifyExpiry'])->name('visa.expiry.notify')->middleware('hasPermission:visa_update');
    Route::get('visa/reports', [VisaController::class, 'reports'])->name('visa.reports')->middleware('hasPermission:visa_read');

    // Tour Management (extra management pages; Package List/Create use functional package.* routes)
    Route::get('tour/category', [TourController::class, 'category'])->name('tour.category')->middleware('hasPermission:tour_read');
    Route::get('tour/category/create', [TourController::class, 'categoryCreate'])->name('tour.categoryCreate')->middleware('hasPermission:tour_read');
    Route::post('tour/category/store', [TourController::class, 'categoryStore'])->name('tour.categoryStore')->middleware('hasPermission:tour_read');
    Route::get('tour/category/edit/{id}', [TourController::class, 'categoryEdit'])->name('tour.categoryEdit')->middleware('hasPermission:tour_read');
    Route::put('tour/category/update/{id}', [TourController::class, 'categoryUpdate'])->name('tour.categoryUpdate')->middleware('hasPermission:tour_read');
    Route::delete('tour/category/delete/{id}', [TourController::class, 'categoryDelete'])->name('tour.categoryDelete')->middleware('hasPermission:tour_read');


    Route::get('tour/schedule', [TourController::class, 'schedule'])->name('tour.schedule')->middleware('hasPermission:tour_read');
    Route::get('tour/schedule/create', [TourController::class, 'scheduleCreate'])->name('tour.scheduleCreate')->middleware('hasPermission:tour_create');
    Route::post('tour/schedule/store', [TourController::class, 'scheduleStore'])->name('tour.scheduleStore')->middleware('hasPermission:tour_create');
    Route::get('tour/schedule/edit/{id}', [TourController::class, 'scheduleEdit'])->name('tour.scheduleEdit')->middleware('hasPermission:tour_update');
    Route::put('tour/schedule/update/{id}', [TourController::class, 'scheduleUpdate'])->name('tour.scheduleUpdate')->middleware('hasPermission:tour_update');
    Route::delete('tour/schedule/delete/{id}', [TourController::class, 'scheduleDelete'])->name('tour.scheduleDelete')->middleware('hasPermission:tour_delete');


    Route::get('tour/guides', [TourController::class, 'guides'])->name('tour.guides')->middleware('hasPermission:tour_read');
    Route::get('tour/guides/create', [TourController::class, 'guidesCreate'])->name('tour.guidesCreate')->middleware('hasPermission:tour_create');
    Route::post('tour/guides/store', [TourController::class, 'guidesStore'])->name('tour.guidesStore')->middleware('hasPermission:tour_create');
    Route::get('tour/guides/edit/{id}', [TourController::class, 'guidesEdit'])->name('tour.guidesEdit')->middleware('hasPermission:tour_update');
    Route::put('tour/guides/update/{id}', [TourController::class, 'guidesUpdate'])->name('tour.guidesUpdate')->middleware('hasPermission:tour_update');
    Route::delete('tour/guides/delete/{id}', [TourController::class, 'guidesDelete'])->name('tour.guidesDelete')->middleware('hasPermission:tour_delete');

    // Guide assignments + schedule (reuses tour_* permissions like schedules below)
    Route::get('tour/guides/assignments', [TourController::class, 'guideAssignments'])->name('tour.guideAssignments')->middleware('hasPermission:tour_read');
    Route::post('tour/guides/assignments/store', [TourController::class, 'guideAssignmentsStore'])->name('tour.guideAssignmentsStore')->middleware('hasPermission:tour_update');
    Route::put('tour/guides/assignments/{id}/status', [TourController::class, 'guideAssignmentsStatus'])->name('tour.guideAssignmentsStatus')->middleware('hasPermission:tour_update');
    Route::delete('tour/guides/assignments/{id}/delete', [TourController::class, 'guideAssignmentsDelete'])->name('tour.guideAssignmentsDelete')->middleware('hasPermission:tour_update');


    // Tour Schedules CRUD (sub-entity, reuses tour_* permissions)
    Route::get('tour/schedules/manage', [TourScheduleController::class, 'index'])->name('tour.schedule.index')->middleware('hasPermission:tour_read');
    Route::get('tour/schedules/manage/create', [TourScheduleController::class, 'create'])->name('tour.schedule.create')->middleware('hasPermission:tour_create');
    Route::post('tour/schedules/manage/store', [TourScheduleController::class, 'store'])->name('tour.schedule.store')->middleware('hasPermission:tour_create');
    Route::get('tour/schedules/manage/{id}/edit', [TourScheduleController::class, 'edit'])->name('tour.schedule.edit')->middleware('hasPermission:tour_update');
    Route::put('tour/schedules/manage/update', [TourScheduleController::class, 'update'])->name('tour.schedule.update')->middleware('hasPermission:tour_update');
    Route::delete('tour/schedules/manage/{id}/delete', [TourScheduleController::class, 'delete'])->name('tour.schedule.delete')->middleware('hasPermission:tour_delete');
    Route::get('tour/reports', [TourController::class, 'reports'])->name('tour.reports')->middleware('hasPermission:tour_read');

    // Hajj & Umrah
    // Hajj Packages CRUD (literal segments before {id})
    Route::get('hajj/manage', [HajjController::class, 'index'])->name('hajj.index')->middleware('hasPermission:hajj_read');
    Route::get('hajj/manage/create', [HajjController::class, 'create'])->name('hajj.create')->middleware('hasPermission:hajj_create');
    Route::post('hajj/manage/store', [HajjController::class, 'store'])->name('hajj.store')->middleware('hasPermission:hajj_create');
    Route::get('hajj/manage/{id}/edit', [HajjController::class, 'edit'])->name('hajj.edit')->middleware('hasPermission:hajj_update');
    Route::put('hajj/manage/update', [HajjController::class, 'update'])->name('hajj.update')->middleware('hasPermission:hajj_update');
    Route::delete('hajj/manage/{id}/delete', [HajjController::class, 'delete'])->name('hajj.delete')->middleware('hasPermission:hajj_delete');
    // Hajj Pilgrims CRUD (sub-entity, reuses hajj_* permissions)
    Route::get('hajj/pilgrims/manage', [HajjPilgrimController::class, 'index'])->name('hajj.pilgrim.index')->middleware('hasPermission:hajj_read');
    Route::get('hajj/pilgrims/manage/create', [HajjPilgrimController::class, 'create'])->name('hajj.pilgrim.create')->middleware('hasPermission:hajj_create');
    Route::post('hajj/pilgrims/manage/store', [HajjPilgrimController::class, 'store'])->name('hajj.pilgrim.store')->middleware('hasPermission:hajj_create');
    Route::get('hajj/pilgrims/manage/{id}/edit', [HajjPilgrimController::class, 'edit'])->name('hajj.pilgrim.edit')->middleware('hasPermission:hajj_update');
    Route::put('hajj/pilgrims/manage/update', [HajjPilgrimController::class, 'update'])->name('hajj.pilgrim.update')->middleware('hasPermission:hajj_update');
    Route::delete('hajj/pilgrims/manage/{id}/delete', [HajjPilgrimController::class, 'delete'])->name('hajj.pilgrim.delete')->middleware('hasPermission:hajj_delete');
    Route::put('hajj/pilgrims/{id}/allocate', [HajjController::class, 'allocate'])->name('hajj.allocate')->middleware('hasPermission:hajj_update');
    Route::get('hajj/hotel-allocation', [HajjController::class, 'hotelAllocation'])->name('hajj.hotel')->middleware('hasPermission:hajj_read');
    Route::get('hajj/flight-allocation', [HajjController::class, 'flightAllocation'])->name('hajj.flight')->middleware('hasPermission:hajj_read');
    Route::post('hajj/flights', [HajjController::class, 'storeFlight'])->name('hajj.flight.store')->middleware('hasPermission:hajj_create');
    Route::delete('hajj/flights/{id}', [HajjController::class, 'deleteFlight'])->name('hajj.flight.delete')->middleware('hasPermission:hajj_delete');
    Route::get('hajj/groups', [HajjController::class, 'groups'])->name('hajj.groups')->middleware('hasPermission:hajj_read');
    Route::post('hajj/groups', [HajjController::class, 'storeGroup'])->name('hajj.group.store')->middleware('hasPermission:hajj_create');
    Route::delete('hajj/groups/{id}', [HajjController::class, 'deleteGroup'])->name('hajj.group.delete')->middleware('hasPermission:hajj_delete');
    Route::get('hajj/payments', [HajjController::class, 'payments'])->name('hajj.payments')->middleware('hasPermission:hajj_read');
    Route::get('hajj/document-verification', [HajjController::class, 'documents'])->name('hajj.documents')->middleware('hasPermission:hajj_read');
    Route::post('hajj/document-verification/{id}/status', [HajjController::class, 'documentStatus'])->name('hajj.documents.status')->middleware('hasPermission:hajj_update');
    Route::get('hajj/reports', [HajjController::class, 'reports'])->name('hajj.reports')->middleware('hasPermission:hajj_read');

    // Hotel Management
    Route::get('hotel/hotels', [HotelController::class, 'index'])->name('hotel.index')->middleware('hasPermission:hotel_read');
    // Hotel CRUD (literal segments before any {id} route)
    Route::get('hotel/hotels/create', [HotelController::class, 'create'])->name('hotel.create')->middleware('hasPermission:hotel_create');
    Route::post('hotel/hotels/store', [HotelController::class, 'store'])->name('hotel.store')->middleware('hasPermission:hotel_create');
    Route::get('hotel/hotels/{id}/edit', [HotelController::class, 'edit'])->name('hotel.edit')->middleware('hasPermission:hotel_update');
    Route::put('hotel/hotels/update', [HotelController::class, 'update'])->name('hotel.update')->middleware('hasPermission:hotel_update');
    Route::delete('hotel/hotels/{id}/delete', [HotelController::class, 'delete'])->name('hotel.delete')->middleware('hasPermission:hotel_delete');
    // Hotel Rooms CRUD (sub-entity, reuses hotel_* permissions)
    Route::get('hotel/rooms/manage', [HotelRoomController::class, 'index'])->name('hotel.room.index')->middleware('hasPermission:hotel_read');
    Route::get('hotel/rooms/manage/create', [HotelRoomController::class, 'create'])->name('hotel.room.create')->middleware('hasPermission:hotel_create');
    Route::post('hotel/rooms/manage/store', [HotelRoomController::class, 'store'])->name('hotel.room.store')->middleware('hasPermission:hotel_create');
    Route::get('hotel/rooms/manage/{id}/edit', [HotelRoomController::class, 'edit'])->name('hotel.room.edit')->middleware('hasPermission:hotel_update');
    Route::put('hotel/rooms/manage/update', [HotelRoomController::class, 'update'])->name('hotel.room.update')->middleware('hasPermission:hotel_update');
    Route::delete('hotel/rooms/manage/{id}/delete', [HotelRoomController::class, 'delete'])->name('hotel.room.delete')->middleware('hasPermission:hotel_delete');
    // Hotel Bookings CRUD (sub-entity, reuses hotel_* permissions)
    Route::get('hotel/bookings/manage', [HotelBookingController::class, 'index'])->name('hotel.booking.index')->middleware('hasPermission:hotel_read');
    Route::get('hotel/bookings/manage/create', [HotelBookingController::class, 'create'])->name('hotel.booking.create')->middleware('hasPermission:hotel_create');
    Route::post('hotel/bookings/manage/store', [HotelBookingController::class, 'store'])->name('hotel.booking.store')->middleware('hasPermission:hotel_create');
    Route::get('hotel/bookings/manage/{id}/edit', [HotelBookingController::class, 'edit'])->name('hotel.booking.edit')->middleware('hasPermission:hotel_update');
    Route::put('hotel/bookings/manage/update', [HotelBookingController::class, 'update'])->name('hotel.booking.update')->middleware('hasPermission:hotel_update');
    Route::delete('hotel/bookings/manage/{id}/delete', [HotelBookingController::class, 'delete'])->name('hotel.booking.delete')->middleware('hasPermission:hotel_delete');
    Route::get('hotel/details/{id}', [HotelController::class, 'details'])->name('hotel.details')->middleware('hasPermission:hotel_read');
    Route::get('hotel/availability', [HotelController::class, 'availability'])->name('hotel.availability')->middleware('hasPermission:hotel_read');
    Route::get('hotel/vouchers', [HotelController::class, 'vouchers'])->name('hotel.vouchers')->middleware('hasPermission:hotel_read');
    Route::get('hotel/vouchers/{id}/pdf', [HotelController::class, 'voucherPdf'])->name('hotel.voucher.pdf')->middleware('hasPermission:hotel_read');
    Route::get('hotel/reports', [HotelController::class, 'reports'])->name('hotel.reports')->middleware('hasPermission:hotel_read');

    // Transport Management
    // Transport Bookings CRUD (literal segments before {id})
    Route::get('transport/manage', [TransportController::class, 'index'])->name('transport.index')->middleware('hasPermission:transport_read');
    Route::get('transport/manage/create', [TransportController::class, 'create'])->name('transport.create')->middleware('hasPermission:transport_create');
    Route::post('transport/manage/store', [TransportController::class, 'store'])->name('transport.store')->middleware('hasPermission:transport_create');
    Route::get('transport/manage/{id}/edit', [TransportController::class, 'edit'])->name('transport.edit')->middleware('hasPermission:transport_update');
    Route::put('transport/manage/update', [TransportController::class, 'update'])->name('transport.update')->middleware('hasPermission:transport_update');
    Route::delete('transport/manage/{id}/delete', [TransportController::class, 'delete'])->name('transport.delete')->middleware('hasPermission:transport_delete');
    Route::get('transport/manage/{id}', [TransportController::class, 'show'])->name('transport.show')->middleware('hasPermission:transport_read')->whereNumber('id');
    Route::get('transport/bus', [TransportController::class, 'bus'])->name('transport.bus')->middleware('hasPermission:transport_read');
    Route::get('transport/train', [TransportController::class, 'train'])->name('transport.train')->middleware('hasPermission:transport_read');
    Route::get('transport/launch', [TransportController::class, 'launch'])->name('transport.launch')->middleware('hasPermission:transport_read');
    Route::get('transport/car-rental', [TransportController::class, 'car'])->name('transport.car')->middleware('hasPermission:transport_read');
    Route::get('transport/airport-transfer', [TransportController::class, 'airport'])->name('transport.airport')->middleware('hasPermission:transport_read');
    Route::get('transport/reports', [TransportController::class, 'reports'])->name('transport.reports')->middleware('hasPermission:transport_read');
    // Drivers CRUD (sub-entity, reuses transport_* permissions)
    Route::get('transport/drivers/manage', [DriverController::class, 'index'])->name('transport.driver.index')->middleware('hasPermission:transport_read');
    Route::get('transport/drivers/manage/create', [DriverController::class, 'create'])->name('transport.driver.create')->middleware('hasPermission:transport_create');
    Route::post('transport/drivers/manage/store', [DriverController::class, 'store'])->name('transport.driver.store')->middleware('hasPermission:transport_create');
    Route::get('transport/drivers/manage/{id}/edit', [DriverController::class, 'edit'])->name('transport.driver.edit')->middleware('hasPermission:transport_update');
    Route::put('transport/drivers/manage/update', [DriverController::class, 'update'])->name('transport.driver.update')->middleware('hasPermission:transport_update');
    Route::delete('transport/drivers/manage/{id}/delete', [DriverController::class, 'delete'])->name('transport.driver.delete')->middleware('hasPermission:transport_delete');
    // Vehicle Categories CRUD (sub-entity, reuses transport_* permissions)
    Route::get('transport/vehicle-categories/manage', [VehicleCategoryController::class, 'index'])->name('transport.vehicle-category.index')->middleware('hasPermission:transport_read');
    Route::get('transport/vehicle-categories/manage/create', [VehicleCategoryController::class, 'create'])->name('transport.vehicle-category.create')->middleware('hasPermission:transport_create');
    Route::post('transport/vehicle-categories/manage/store', [VehicleCategoryController::class, 'store'])->name('transport.vehicle-category.store')->middleware('hasPermission:transport_create');
    Route::get('transport/vehicle-categories/manage/{id}/edit', [VehicleCategoryController::class, 'edit'])->name('transport.vehicle-category.edit')->middleware('hasPermission:transport_update');
    Route::put('transport/vehicle-categories/manage/update', [VehicleCategoryController::class, 'update'])->name('transport.vehicle-category.update')->middleware('hasPermission:transport_update');
    Route::delete('transport/vehicle-categories/manage/{id}/delete', [VehicleCategoryController::class, 'delete'])->name('transport.vehicle-category.delete')->middleware('hasPermission:transport_delete');

    // Flight Management
    // Flight Bookings CRUD (literal segments before {id})
    Route::get('flight/bookings', [FlightController::class, 'index'])->name('flight.index')->middleware('hasPermission:flight_read');
    Route::get('flight/bookings/create', [FlightController::class, 'create'])->name('flight.create')->middleware('hasPermission:flight_create');
    Route::post('flight/bookings/store', [FlightController::class, 'store'])->name('flight.store')->middleware('hasPermission:flight_create');
    Route::get('flight/bookings/{id}/edit', [FlightController::class, 'edit'])->name('flight.edit')->middleware('hasPermission:flight_update');
    Route::put('flight/bookings/update', [FlightController::class, 'update'])->name('flight.update')->middleware('hasPermission:flight_update');
    Route::delete('flight/bookings/{id}/delete', [FlightController::class, 'delete'])->name('flight.delete')->middleware('hasPermission:flight_delete');

    Route::get('flight/booking-details/{id}', [FlightController::class, 'booking'])->name('flight.booking')->middleware('hasPermission:flight_read');
    Route::get('flight/reissue', [FlightController::class, 'reissue'])->name('flight.reissue')->middleware('hasPermission:flight_read');
    Route::put('flight/tickets/{id}/settle', [FlightController::class, 'settle'])->name('flight.settle')->middleware('hasPermission:flight_update');
    Route::get('flight/cancellation', [FlightController::class, 'cancellation'])->name('flight.cancellation')->middleware('hasPermission:flight_read');
    Route::get('flight/refund-tracking', [FlightController::class, 'refund'])->name('flight.refund')->middleware('hasPermission:flight_read');
    Route::get('flight/reports', [FlightController::class, 'reports'])->name('flight.reports')->middleware('hasPermission:flight_read');

    // Accounting
    // Chart of Accounts CRUD (literal segments before {id})
    Route::get('accounting/manage-accounts', [AccountingController::class, 'index'])->name('acc.index')->middleware('hasPermission:accounting_read');
    Route::get('accounting/manage-accounts/create', [AccountingController::class, 'create'])->name('acc.create')->middleware('hasPermission:accounting_create');
    Route::post('accounting/manage-accounts/store', [AccountingController::class, 'store'])->name('acc.store')->middleware('hasPermission:accounting_create');
    Route::get('accounting/manage-accounts/{id}/edit', [AccountingController::class, 'edit'])->name('acc.edit')->middleware('hasPermission:accounting_update');
    Route::put('accounting/manage-accounts/update', [AccountingController::class, 'update'])->name('acc.update')->middleware('hasPermission:accounting_update');
    Route::delete('accounting/manage-accounts/{id}/delete', [AccountingController::class, 'delete'])->name('acc.delete')->middleware('hasPermission:accounting_delete');
    // Account Transactions CRUD (sub-entity, reuses accounting_* permissions)
    Route::get('accounting/transactions/manage', [AccountTransactionController::class, 'index'])->name('acc.txn.index')->middleware('hasPermission:accounting_read');
    Route::get('accounting/transactions/manage/create', [AccountTransactionController::class, 'create'])->name('acc.txn.create')->middleware('hasPermission:accounting_create');
    Route::post('accounting/transactions/manage/store', [AccountTransactionController::class, 'store'])->name('acc.txn.store')->middleware('hasPermission:accounting_create');
    Route::get('accounting/transactions/manage/{id}/edit', [AccountTransactionController::class, 'edit'])->name('acc.txn.edit')->middleware('hasPermission:accounting_update');
    Route::put('accounting/transactions/manage/update', [AccountTransactionController::class, 'update'])->name('acc.txn.update')->middleware('hasPermission:accounting_update');
    Route::delete('accounting/transactions/manage/{id}/delete', [AccountTransactionController::class, 'delete'])->name('acc.txn.delete')->middleware('hasPermission:accounting_delete');
    // Invoices CRUD (sub-entity, reuses accounting_* permissions)
    // ---- Printable documents (PDF) ----------------------------------------
    // Each carries the same permission as the module the document belongs to.
    Route::get('documents/invoice/{id}',       [DocumentController::class, 'invoice'])->name('doc.invoice')->middleware('hasPermission:accounting_read');
    Route::get('documents/receipt/{id}',       [DocumentController::class, 'receipt'])->name('doc.receipt')->middleware('hasPermission:accounting_read');
    Route::get('documents/hotel-voucher/{id}', [DocumentController::class, 'hotelVoucher'])->name('doc.hotel.voucher')->middleware('hasPermission:hotel_read');
    Route::get('documents/eticket/{id}',       [DocumentController::class, 'eTicket'])->name('doc.eticket')->middleware('hasPermission:flight_read');

    Route::get('accounting/invoices/manage', [InvoiceController::class, 'index'])->name('acc.invoice.index')->middleware('hasPermission:accounting_read');
    Route::get('accounting/invoices/manage/create', [InvoiceController::class, 'create'])->name('acc.invoice.create')->middleware('hasPermission:accounting_create');
    Route::post('accounting/invoices/manage/store', [InvoiceController::class, 'store'])->name('acc.invoice.store')->middleware('hasPermission:accounting_create');
    Route::get('accounting/invoices/manage/{id}/edit', [InvoiceController::class, 'edit'])->name('acc.invoice.edit')->middleware('hasPermission:accounting_update');
    Route::put('accounting/invoices/manage/update', [InvoiceController::class, 'update'])->name('acc.invoice.update')->middleware('hasPermission:accounting_update');
    Route::delete('accounting/invoices/manage/{id}/delete', [InvoiceController::class, 'delete'])->name('acc.invoice.delete')->middleware('hasPermission:accounting_delete');
    // Refunds are recorded against the invoice they reverse, never by editing it.
    Route::post('accounting/invoices/manage/{id}/refund', [InvoiceController::class, 'refund'])->name('acc.invoice.refund')->middleware('hasPermission:accounting_update');
    // Receipts CRUD (sub-entity, reuses accounting_* permissions)
    Route::get('accounting/receipts/manage', [ReceiptController::class, 'index'])->name('acc.receipt.index')->middleware('hasPermission:accounting_read');
    Route::get('accounting/receipts/manage/create', [ReceiptController::class, 'create'])->name('acc.receipt.create')->middleware('hasPermission:accounting_create');
    Route::post('accounting/receipts/manage/store', [ReceiptController::class, 'store'])->name('acc.receipt.store')->middleware('hasPermission:accounting_create');
    Route::get('accounting/receipts/manage/{id}/edit', [ReceiptController::class, 'edit'])->name('acc.receipt.edit')->middleware('hasPermission:accounting_update');
    Route::put('accounting/receipts/manage/update', [ReceiptController::class, 'update'])->name('acc.receipt.update')->middleware('hasPermission:accounting_update');
    Route::delete('accounting/receipts/manage/{id}/delete', [ReceiptController::class, 'delete'])->name('acc.receipt.delete')->middleware('hasPermission:accounting_delete');
    Route::get('accounting/dashboard', [AccountingController::class, 'dashboard'])->name('acc.dashboard')->middleware('hasPermission:accounting_read');
    Route::get('accounting/income', [AccountingController::class, 'income'])->name('acc.income')->middleware('hasPermission:accounting_read');
    Route::get('accounting/expenses', [AccountingController::class, 'expenses'])->name('acc.expenses')->middleware('hasPermission:accounting_read');
    Route::get('accounting/journal-entries', [AccountingController::class, 'journal'])->name('acc.journal')->middleware('hasPermission:accounting_read');
    Route::get('accounting/cash-book', [AccountingController::class, 'cashbook'])->name('acc.cashbook')->middleware('hasPermission:accounting_read');
    Route::get('accounting/bank-book', [AccountingController::class, 'bankbook'])->name('acc.bankbook')->middleware('hasPermission:accounting_read');
    Route::get('accounting/ledger', [AccountingController::class, 'ledger'])->name('acc.ledger')->middleware('hasPermission:accounting_read');
    Route::get('accounting/trial-balance', [AccountingController::class, 'trial'])->name('acc.trial')->middleware('hasPermission:accounting_read');
    Route::get('accounting/profit-loss', [AccountingController::class, 'pl'])->name('acc.pl')->middleware('hasPermission:accounting_read');
    Route::get('accounting/balance-sheet', [AccountingController::class, 'balance'])->name('acc.balance')->middleware('hasPermission:accounting_read');
    Route::get('accounting/refunds', [AccountingController::class, 'refunds'])->name('acc.refunds')->middleware('hasPermission:accounting_read');
    Route::get('accounting/tax-reports', [AccountingController::class, 'tax'])->name('acc.tax')->middleware('hasPermission:accounting_read');

    // Supplier Management
    // Suppliers CRUD (literal segments before {id})
    Route::get('supplier/manage', [SupplierController::class, 'index'])->name('supplier.index')->middleware('hasPermission:supplier_read');
    Route::get('supplier/manage/create', [SupplierController::class, 'create'])->name('supplier.create')->middleware('hasPermission:supplier_create');
    Route::post('supplier/manage/store', [SupplierController::class, 'store'])->name('supplier.store')->middleware('hasPermission:supplier_create');
    Route::get('supplier/manage/{id}/edit', [SupplierController::class, 'edit'])->name('supplier.edit')->middleware('hasPermission:supplier_update');
    Route::put('supplier/manage/update', [SupplierController::class, 'update'])->name('supplier.update')->middleware('hasPermission:supplier_update');
    Route::delete('supplier/manage/{id}/delete', [SupplierController::class, 'delete'])->name('supplier.delete')->middleware('hasPermission:supplier_delete');
    Route::get('supplier/airlines', [SupplierController::class, 'airlines'])->name('sup.airlines')->middleware('hasPermission:supplier_read');
    Route::get('supplier/hotels', [SupplierController::class, 'hotels'])->name('sup.hotels')->middleware('hasPermission:supplier_read');
    Route::get('supplier/transport-vendors', [SupplierController::class, 'transport'])->name('sup.transport')->middleware('hasPermission:supplier_read');
    Route::get('supplier/visa-partners', [SupplierController::class, 'visa'])->name('sup.visa')->middleware('hasPermission:supplier_read');
    Route::get('supplier/contracts', [SupplierController::class, 'contracts'])->name('sup.contracts')->middleware('hasPermission:supplier_read');
    Route::get('supplier/contracts/create', [SupplierController::class, 'contractCreate'])->name('sup.contract.create')->middleware('hasPermission:supplier_create');
    Route::post('supplier/contracts/store', [SupplierController::class, 'contractStore'])->name('sup.contract.store')->middleware('hasPermission:supplier_create');
    Route::get('supplier/contracts/{id}/edit', [SupplierController::class, 'contractEdit'])->name('sup.contract.edit')->middleware('hasPermission:supplier_update')->whereNumber('id');
    Route::put('supplier/contracts/update', [SupplierController::class, 'contractUpdate'])->name('sup.contract.update')->middleware('hasPermission:supplier_update');
    Route::delete('supplier/contracts/{id}/delete', [SupplierController::class, 'contractDelete'])->name('sup.contract.delete')->middleware('hasPermission:supplier_delete');
    Route::post('supplier/ledger/store', [SupplierController::class, 'ledgerStore'])->name('sup.ledger.store')->middleware('hasPermission:supplier_create');
    Route::delete('supplier/ledger/{id}/delete', [SupplierController::class, 'ledgerDelete'])->name('sup.ledger.delete')->middleware('hasPermission:supplier_delete');
    Route::get('supplier/ledger', [SupplierController::class, 'ledger'])->name('sup.ledger')->middleware('hasPermission:supplier_read');
    Route::get('supplier/reports', [SupplierController::class, 'reports'])->name('sup.reports')->middleware('hasPermission:supplier_read');

    // Task Management
    // Tasks CRUD (literal segments before {id})
    Route::get('task/manage', [TaskController::class, 'index'])->name('task.index')->middleware('hasPermission:task_read');
    Route::get('task/manage/create', [TaskController::class, 'create'])->name('task.create')->middleware('hasPermission:task_create');
    Route::post('task/manage/store', [TaskController::class, 'store'])->name('task.store')->middleware('hasPermission:task_create');
    Route::get('task/manage/{id}/edit', [TaskController::class, 'edit'])->name('task.edit')->middleware('hasPermission:task_update');
    Route::put('task/manage/update', [TaskController::class, 'update'])->name('task.update')->middleware('hasPermission:task_update');
    Route::delete('task/manage/{id}/delete', [TaskController::class, 'delete'])->name('task.delete')->middleware('hasPermission:task_delete');
    Route::get('task/projects', [TaskController::class, 'projects'])->name('task.projects')->middleware('hasPermission:task_read');
    Route::get('task/assignments', [TaskController::class, 'assignments'])->name('task.assignments')->middleware('hasPermission:task_read');
    Route::get('task/deadlines', [TaskController::class, 'deadlines'])->name('task.deadlines')->middleware('hasPermission:task_read');
    Route::get('task/calendar', [TaskController::class, 'calendar'])->name('task.calendar')->middleware('hasPermission:task_read');
    Route::get('task/kanban', [TaskController::class, 'kanban'])->name('task.kanban')->middleware('hasPermission:task_read');

    // Support Center
    // Support Tickets CRUD (literal segments before {id})
    Route::get('support/manage', [SupportController::class, 'index'])->name('support.index')->middleware('hasPermission:support_read');
    Route::get('support/manage/create', [SupportController::class, 'create'])->name('support.create')->middleware('hasPermission:support_create');
    Route::post('support/manage/store', [SupportController::class, 'store'])->name('support.store')->middleware('hasPermission:support_create');
    Route::get('support/manage/{id}/edit', [SupportController::class, 'edit'])->name('support.edit')->middleware('hasPermission:support_update');
    Route::put('support/manage/update', [SupportController::class, 'update'])->name('support.update')->middleware('hasPermission:support_update');
    Route::delete('support/manage/{id}/delete', [SupportController::class, 'delete'])->name('support.delete')->middleware('hasPermission:support_delete');
    // Support Announcements CRUD (sub-entity, reuses support_* permissions)
    Route::get('support/announcements/manage', [AnnouncementController::class, 'index'])->name('support.announcement.index')->middleware('hasPermission:support_read');
    Route::get('support/announcements/manage/create', [AnnouncementController::class, 'create'])->name('support.announcement.create')->middleware('hasPermission:support_create');
    Route::post('support/announcements/manage/store', [AnnouncementController::class, 'store'])->name('support.announcement.store')->middleware('hasPermission:support_create');
    Route::get('support/announcements/manage/{id}/edit', [AnnouncementController::class, 'edit'])->name('support.announcement.edit')->middleware('hasPermission:support_update');
    Route::put('support/announcements/manage/update', [AnnouncementController::class, 'update'])->name('support.announcement.update')->middleware('hasPermission:support_update');
    Route::delete('support/announcements/manage/{id}/delete', [AnnouncementController::class, 'delete'])->name('support.announcement.delete')->middleware('hasPermission:support_delete');
    // Knowledge Base Articles CRUD (sub-entity, reuses support_* permissions)
    Route::get('support/kb/manage', [KbArticleController::class, 'index'])->name('support.kb.index')->middleware('hasPermission:support_read');
    Route::get('support/kb/manage/create', [KbArticleController::class, 'create'])->name('support.kb.create')->middleware('hasPermission:support_create');
    Route::post('support/kb/manage/store', [KbArticleController::class, 'store'])->name('support.kb.store')->middleware('hasPermission:support_create');
    Route::get('support/kb/manage/{id}/edit', [KbArticleController::class, 'edit'])->name('support.kb.edit')->middleware('hasPermission:support_update');
    Route::put('support/kb/manage/update', [KbArticleController::class, 'update'])->name('support.kb.update')->middleware('hasPermission:support_update');
    Route::delete('support/kb/manage/{id}/delete', [KbArticleController::class, 'delete'])->name('support.kb.delete')->middleware('hasPermission:support_delete');

    // ===== Back-office: Human Resources (hr_* permissions) =====
    Route::get('hr/attendance/manage', [StaffAttendanceController::class, 'index'])->name('hr.attendance.index')->middleware('hasPermission:hr_read');
    Route::get('hr/attendance/manage/create', [StaffAttendanceController::class, 'create'])->name('hr.attendance.create')->middleware('hasPermission:hr_create');
    Route::post('hr/attendance/manage/store', [StaffAttendanceController::class, 'store'])->name('hr.attendance.store')->middleware('hasPermission:hr_create');
    Route::get('hr/attendance/manage/{id}/edit', [StaffAttendanceController::class, 'edit'])->name('hr.attendance.edit')->middleware('hasPermission:hr_update');
    Route::put('hr/attendance/manage/update', [StaffAttendanceController::class, 'update'])->name('hr.attendance.update')->middleware('hasPermission:hr_update');
    Route::delete('hr/attendance/manage/{id}/delete', [StaffAttendanceController::class, 'delete'])->name('hr.attendance.delete')->middleware('hasPermission:hr_delete');
    Route::get('hr/leave/manage', [LeaveRequestController::class, 'index'])->name('hr.leave.index')->middleware('hasPermission:hr_read');
    Route::get('hr/leave/manage/create', [LeaveRequestController::class, 'create'])->name('hr.leave.create')->middleware('hasPermission:hr_create');
    Route::post('hr/leave/manage/store', [LeaveRequestController::class, 'store'])->name('hr.leave.store')->middleware('hasPermission:hr_create');
    Route::get('hr/leave/manage/{id}/edit', [LeaveRequestController::class, 'edit'])->name('hr.leave.edit')->middleware('hasPermission:hr_update');
    Route::put('hr/leave/manage/update', [LeaveRequestController::class, 'update'])->name('hr.leave.update')->middleware('hasPermission:hr_update');
    Route::delete('hr/leave/manage/{id}/delete', [LeaveRequestController::class, 'delete'])->name('hr.leave.delete')->middleware('hasPermission:hr_delete');
    Route::get('hr/payslip/manage', [PayslipController::class, 'index'])->name('hr.payslip.index')->middleware('hasPermission:hr_read');
    Route::get('hr/payslip/manage/create', [PayslipController::class, 'create'])->name('hr.payslip.create')->middleware('hasPermission:hr_create');
    Route::post('hr/payslip/manage/store', [PayslipController::class, 'store'])->name('hr.payslip.store')->middleware('hasPermission:hr_create');
    Route::get('hr/payslip/manage/{id}/edit', [PayslipController::class, 'edit'])->name('hr.payslip.edit')->middleware('hasPermission:hr_update');
    Route::put('hr/payslip/manage/update', [PayslipController::class, 'update'])->name('hr.payslip.update')->middleware('hasPermission:hr_update');
    Route::delete('hr/payslip/manage/{id}/delete', [PayslipController::class, 'delete'])->name('hr.payslip.delete')->middleware('hasPermission:hr_delete');

    // ===== Back-office: Agents roster (read-only; agents live in Users & Roles) =====
    Route::get('agent-finance/agents', [AgentController::class, 'index'])->name('agent.list')->middleware('hasPermission:agent_finance_read');

    // ===== Back-office: Agent Finance (agent_finance_* permissions) =====
    Route::get('agent-finance/commissions/manage', [AgentCommissionController::class, 'index'])->name('agent.commission.index')->middleware('hasPermission:agent_finance_read');
    Route::get('agent-finance/commissions/manage/create', [AgentCommissionController::class, 'create'])->name('agent.commission.create')->middleware('hasPermission:agent_finance_create');
    Route::post('agent-finance/commissions/manage/store', [AgentCommissionController::class, 'store'])->name('agent.commission.store')->middleware('hasPermission:agent_finance_create');
    Route::get('agent-finance/commissions/manage/{id}/edit', [AgentCommissionController::class, 'edit'])->name('agent.commission.edit')->middleware('hasPermission:agent_finance_update');
    Route::put('agent-finance/commissions/manage/update', [AgentCommissionController::class, 'update'])->name('agent.commission.update')->middleware('hasPermission:agent_finance_update');
    Route::delete('agent-finance/commissions/manage/{id}/delete', [AgentCommissionController::class, 'delete'])->name('agent.commission.delete')->middleware('hasPermission:agent_finance_delete');
    // Commission settlement: approving credits the agent's wallet.
    Route::post('agent-finance/commissions/manage/{id}/approve', [AgentCommissionController::class, 'approve'])->name('agent.commission.approve')->middleware('hasPermission:agent_finance_update');
    Route::post('agent-finance/commissions/manage/{id}/unapprove', [AgentCommissionController::class, 'unapprove'])->name('agent.commission.unapprove')->middleware('hasPermission:agent_finance_update');

    // ===== Back-office: Agent payouts (wallet withdrawals) =====
    Route::get('agent-finance/withdrawals', [AgentWithdrawalController::class, 'index'])->name('agent.withdrawal.index')->middleware('hasPermission:agent_finance_read');
    Route::get('agent-finance/withdrawals/create', [AgentWithdrawalController::class, 'create'])->name('agent.withdrawal.create')->middleware('hasPermission:agent_finance_create');
    Route::post('agent-finance/withdrawals/store', [AgentWithdrawalController::class, 'store'])->name('agent.withdrawal.store')->middleware('hasPermission:agent_finance_create');
    Route::get('agent-finance/withdrawals/{id}/edit', [AgentWithdrawalController::class, 'edit'])->name('agent.withdrawal.edit')->middleware('hasPermission:agent_finance_update');
    Route::put('agent-finance/withdrawals/update', [AgentWithdrawalController::class, 'update'])->name('agent.withdrawal.update')->middleware('hasPermission:agent_finance_update');
    Route::post('agent-finance/withdrawals/{id}/approve', [AgentWithdrawalController::class, 'approve'])->name('agent.withdrawal.approve')->middleware('hasPermission:agent_finance_update');
    Route::post('agent-finance/withdrawals/{id}/pay', [AgentWithdrawalController::class, 'pay'])->name('agent.withdrawal.pay')->middleware('hasPermission:agent_finance_update');
    Route::post('agent-finance/withdrawals/{id}/reject', [AgentWithdrawalController::class, 'reject'])->name('agent.withdrawal.reject')->middleware('hasPermission:agent_finance_update');
    Route::delete('agent-finance/withdrawals/{id}/delete', [AgentWithdrawalController::class, 'delete'])->name('agent.withdrawal.delete')->middleware('hasPermission:agent_finance_delete');

    Route::get('agent-finance/invoices/manage', [AgentInvoiceController::class, 'index'])->name('agent.invoice.index')->middleware('hasPermission:agent_finance_read');
    Route::get('agent-finance/invoices/manage/create', [AgentInvoiceController::class, 'create'])->name('agent.invoice.create')->middleware('hasPermission:agent_finance_create');
    Route::post('agent-finance/invoices/manage/store', [AgentInvoiceController::class, 'store'])->name('agent.invoice.store')->middleware('hasPermission:agent_finance_create');
    Route::get('agent-finance/invoices/manage/{id}/edit', [AgentInvoiceController::class, 'edit'])->name('agent.invoice.edit')->middleware('hasPermission:agent_finance_update');
    Route::put('agent-finance/invoices/manage/update', [AgentInvoiceController::class, 'update'])->name('agent.invoice.update')->middleware('hasPermission:agent_finance_update');
    Route::delete('agent-finance/invoices/manage/{id}/delete', [AgentInvoiceController::class, 'delete'])->name('agent.invoice.delete')->middleware('hasPermission:agent_finance_delete');

    // ===== Customer records: Travelers + Passports (customer_* permissions) =====
    Route::get('customer/travelers/manage', [TravelerController::class, 'index'])->name('customer.traveler.index')->middleware('hasPermission:customer_read');
    Route::get('customer/travelers/manage/create', [TravelerController::class, 'create'])->name('customer.traveler.create')->middleware('hasPermission:customer_create');
    Route::post('customer/travelers/manage/store', [TravelerController::class, 'store'])->name('customer.traveler.store')->middleware('hasPermission:customer_create');
    Route::get('customer/travelers/manage/{id}/edit', [TravelerController::class, 'edit'])->name('customer.traveler.edit')->middleware('hasPermission:customer_update');
    Route::put('customer/travelers/manage/update', [TravelerController::class, 'update'])->name('customer.traveler.update')->middleware('hasPermission:customer_update');
    Route::delete('customer/travelers/manage/{id}/delete', [TravelerController::class, 'delete'])->name('customer.traveler.delete')->middleware('hasPermission:customer_delete');
    Route::get('customer/passports/manage', [PassportController::class, 'index'])->name('customer.passport.index')->middleware('hasPermission:customer_read');
    Route::get('customer/passports/manage/create', [PassportController::class, 'create'])->name('customer.passport.create')->middleware('hasPermission:customer_create');
    Route::post('customer/passports/manage/store', [PassportController::class, 'store'])->name('customer.passport.store')->middleware('hasPermission:customer_create');
    Route::get('customer/passports/manage/{id}/edit', [PassportController::class, 'edit'])->name('customer.passport.edit')->middleware('hasPermission:customer_update');
    Route::put('customer/passports/manage/update', [PassportController::class, 'update'])->name('customer.passport.update')->middleware('hasPermission:customer_update');
    Route::delete('customer/passports/manage/{id}/delete', [PassportController::class, 'delete'])->name('customer.passport.delete')->middleware('hasPermission:customer_delete');
    Route::get('support/ticket-details/{id}', [SupportController::class, 'ticket'])->name('support.ticket')->middleware('hasPermission:support_read');
    Route::get('support/knowledge-base', [SupportController::class, 'kb'])->name('support.kb')->middleware('hasPermission:support_read');

    // Reporting Center
    Route::get('reports-center/sales', [ReportController::class, 'sales'])->name('report.sales')->middleware('hasPermission:report_read');
    Route::get('reports-center/visa', [ReportController::class, 'visa'])->name('report.visa')->middleware('hasPermission:report_read');
    Route::get('reports-center/package', [ReportController::class, 'package'])->name('report.package')->middleware('hasPermission:report_read');
    Route::get('reports-center/flight', [ReportController::class, 'flight'])->name('report.flight')->middleware('hasPermission:report_read');
    Route::get('reports-center/hotel', [ReportController::class, 'hotel'])->name('report.hotel')->middleware('hasPermission:report_read');
    Route::get('reports-center/agent', [ReportController::class, 'agent'])->name('report.agent')->middleware('hasPermission:report_read');
    Route::get('reports-center/customer', [ReportController::class, 'customer'])->name('report.customer')->middleware('hasPermission:report_read');
    Route::get('reports-center/financial', [ReportController::class, 'financial'])->name('report.financial')->middleware('hasPermission:report_read');
    Route::get('reports-center/custom', [ReportController::class, 'custom'])->name('report.custom')->middleware('hasPermission:report_read');

    // CMS
    // CMS Pages CRUD (literal segments before {id})
    Route::get('cms/manage', [CmsController::class, 'index'])->name('cms.index')->middleware('hasPermission:cms_read');
    Route::get('cms/manage/create', [CmsController::class, 'create'])->name('cms.create')->middleware('hasPermission:cms_create');
    Route::post('cms/manage/store', [CmsController::class, 'store'])->name('cms.store')->middleware('hasPermission:cms_create');
    Route::get('cms/manage/{id}/edit', [CmsController::class, 'edit'])->name('cms.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/manage/update', [CmsController::class, 'update'])->name('cms.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/manage/{id}/delete', [CmsController::class, 'delete'])->name('cms.delete')->middleware('hasPermission:cms_delete');
    // CMS Blogs CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/blogs/manage', [BlogController::class, 'index'])->name('cms.blog.index')->middleware('hasPermission:cms_read');
    Route::get('cms/blogs/manage/create', [BlogController::class, 'create'])->name('cms.blog.create')->middleware('hasPermission:cms_create');
    Route::post('cms/blogs/manage/store', [BlogController::class, 'store'])->name('cms.blog.store')->middleware('hasPermission:cms_create');
    Route::get('cms/blogs/manage/{id}/edit', [BlogController::class, 'edit'])->name('cms.blog.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/blogs/manage/update', [BlogController::class, 'update'])->name('cms.blog.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/blogs/manage/{id}/delete', [BlogController::class, 'delete'])->name('cms.blog.delete')->middleware('hasPermission:cms_delete');
    // CMS Sliders CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/sliders/manage', [SliderController::class, 'index'])->name('cms.slider.index')->middleware('hasPermission:cms_read');
    Route::get('cms/sliders/manage/create', [SliderController::class, 'create'])->name('cms.slider.create')->middleware('hasPermission:cms_create');
    Route::post('cms/sliders/manage/store', [SliderController::class, 'store'])->name('cms.slider.store')->middleware('hasPermission:cms_create');
    Route::get('cms/sliders/manage/{id}/edit', [SliderController::class, 'edit'])->name('cms.slider.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/sliders/manage/update', [SliderController::class, 'update'])->name('cms.slider.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/sliders/manage/{id}/delete', [SliderController::class, 'delete'])->name('cms.slider.delete')->middleware('hasPermission:cms_delete');
    // CMS Testimonials CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/testimonials/manage', [TestimonialController::class, 'index'])->name('cms.testimonial.index')->middleware('hasPermission:cms_read');
    Route::get('cms/testimonials/manage/create', [TestimonialController::class, 'create'])->name('cms.testimonial.create')->middleware('hasPermission:cms_create');
    Route::post('cms/testimonials/manage/store', [TestimonialController::class, 'store'])->name('cms.testimonial.store')->middleware('hasPermission:cms_create');
    Route::get('cms/testimonials/manage/{id}/edit', [TestimonialController::class, 'edit'])->name('cms.testimonial.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/testimonials/manage/update', [TestimonialController::class, 'update'])->name('cms.testimonial.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/testimonials/manage/{id}/delete', [TestimonialController::class, 'delete'])->name('cms.testimonial.delete')->middleware('hasPermission:cms_delete');
    // CMS Gallery CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/gallery/manage', [GalleryController::class, 'index'])->name('cms.gallery.index')->middleware('hasPermission:cms_read');
    Route::get('cms/gallery/manage/create', [GalleryController::class, 'create'])->name('cms.gallery.create')->middleware('hasPermission:cms_create');
    Route::post('cms/gallery/manage/store', [GalleryController::class, 'store'])->name('cms.gallery.store')->middleware('hasPermission:cms_create');
    Route::get('cms/gallery/manage/{id}/edit', [GalleryController::class, 'edit'])->name('cms.gallery.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/gallery/manage/update', [GalleryController::class, 'update'])->name('cms.gallery.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/gallery/manage/{id}/delete', [GalleryController::class, 'delete'])->name('cms.gallery.delete')->middleware('hasPermission:cms_delete');
    // CMS FAQs CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/faqs/manage', [FaqController::class, 'index'])->name('cms.faq.index')->middleware('hasPermission:cms_read');
    Route::get('cms/faqs/manage/create', [FaqController::class, 'create'])->name('cms.faq.create')->middleware('hasPermission:cms_create');
    Route::post('cms/faqs/manage/store', [FaqController::class, 'store'])->name('cms.faq.store')->middleware('hasPermission:cms_create');
    Route::get('cms/faqs/manage/{id}/edit', [FaqController::class, 'edit'])->name('cms.faq.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/faqs/manage/update', [FaqController::class, 'update'])->name('cms.faq.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/faqs/manage/{id}/delete', [FaqController::class, 'delete'])->name('cms.faq.delete')->middleware('hasPermission:cms_delete');
    // CMS Menus CRUD (sub-entity, reuses cms_* permissions)
    Route::get('cms/menus/manage', [MenuController::class, 'index'])->name('cms.menu.index')->middleware('hasPermission:cms_read');
    Route::get('cms/menus/manage/create', [MenuController::class, 'create'])->name('cms.menu.create')->middleware('hasPermission:cms_create');
    Route::post('cms/menus/manage/store', [MenuController::class, 'store'])->name('cms.menu.store')->middleware('hasPermission:cms_create');
    Route::get('cms/menus/manage/{id}/edit', [MenuController::class, 'edit'])->name('cms.menu.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/menus/manage/update', [MenuController::class, 'update'])->name('cms.menu.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/menus/manage/{id}/delete', [MenuController::class, 'delete'])->name('cms.menu.delete')->middleware('hasPermission:cms_delete');
    // Footer social links CRUD. Uses the existing Settings permissions.
    Route::get('settings/social-links', [SocialLinkController::class, 'index'])->name('settings.social-links.index')->middleware('hasPermission:general_settings_read');
    Route::get('settings/social-links/create', [SocialLinkController::class, 'create'])->name('settings.social-links.create')->middleware('hasPermission:general_settings_update');
    Route::post('settings/social-links', [SocialLinkController::class, 'store'])->name('settings.social-links.store')->middleware('hasPermission:general_settings_update');
    Route::get('settings/social-links/{socialLink}/edit', [SocialLinkController::class, 'edit'])->name('settings.social-links.edit')->middleware('hasPermission:general_settings_update');
    Route::put('settings/social-links/{socialLink}', [SocialLinkController::class, 'update'])->name('settings.social-links.update')->middleware(['hasPermission:general_settings_update', 'demo.readonly']);
    Route::delete('settings/social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('settings.social-links.destroy')->middleware(['hasPermission:general_settings_update', 'demo.readonly']);
    // ---- Public-website catalogues: the content the marketing site renders ----
    Route::get('cms/visa-services/manage', [VisaServiceController::class, 'index'])->name('cms.visa-service.index')->middleware('hasPermission:cms_read');
    Route::get('cms/visa-services/manage/create', [VisaServiceController::class, 'create'])->name('cms.visa-service.create')->middleware('hasPermission:cms_create');
    Route::post('cms/visa-services/manage/store', [VisaServiceController::class, 'store'])->name('cms.visa-service.store')->middleware('hasPermission:cms_create');
    Route::get('cms/visa-services/manage/{id}/edit', [VisaServiceController::class, 'edit'])->name('cms.visa-service.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/visa-services/manage/update', [VisaServiceController::class, 'update'])->name('cms.visa-service.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/visa-services/manage/{id}/delete', [VisaServiceController::class, 'delete'])->name('cms.visa-service.delete')->middleware('hasPermission:cms_delete');

    Route::get('cms/flight-routes/manage', [FlightRouteController::class, 'index'])->name('cms.flight-route.index')->middleware('hasPermission:cms_read');
    Route::get('cms/flight-routes/manage/create', [FlightRouteController::class, 'create'])->name('cms.flight-route.create')->middleware('hasPermission:cms_create');
    Route::post('cms/flight-routes/manage/store', [FlightRouteController::class, 'store'])->name('cms.flight-route.store')->middleware('hasPermission:cms_create');
    Route::get('cms/flight-routes/manage/{id}/edit', [FlightRouteController::class, 'edit'])->name('cms.flight-route.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/flight-routes/manage/update', [FlightRouteController::class, 'update'])->name('cms.flight-route.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/flight-routes/manage/{id}/delete', [FlightRouteController::class, 'delete'])->name('cms.flight-route.delete')->middleware('hasPermission:cms_delete');

    Route::get('cms/transport-services/manage', [TransportServiceController::class, 'index'])->name('cms.transport-service.index')->middleware('hasPermission:cms_read');
    Route::get('cms/transport-services/manage/create', [TransportServiceController::class, 'create'])->name('cms.transport-service.create')->middleware('hasPermission:cms_create');
    Route::post('cms/transport-services/manage/store', [TransportServiceController::class, 'store'])->name('cms.transport-service.store')->middleware('hasPermission:cms_create');
    Route::get('cms/transport-services/manage/{id}/edit', [TransportServiceController::class, 'edit'])->name('cms.transport-service.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/transport-services/manage/update', [TransportServiceController::class, 'update'])->name('cms.transport-service.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/transport-services/manage/{id}/delete', [TransportServiceController::class, 'delete'])->name('cms.transport-service.delete')->middleware('hasPermission:cms_delete');

    Route::get('cms/job-openings/manage', [JobOpeningController::class, 'index'])->name('cms.job-opening.index')->middleware('hasPermission:cms_read');
    Route::get('cms/job-openings/manage/create', [JobOpeningController::class, 'create'])->name('cms.job-opening.create')->middleware('hasPermission:cms_create');
    Route::post('cms/job-openings/manage/store', [JobOpeningController::class, 'store'])->name('cms.job-opening.store')->middleware('hasPermission:cms_create');
    Route::get('cms/job-openings/manage/{id}/edit', [JobOpeningController::class, 'edit'])->name('cms.job-opening.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/job-openings/manage/update', [JobOpeningController::class, 'update'])->name('cms.job-opening.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/job-openings/manage/{id}/delete', [JobOpeningController::class, 'delete'])->name('cms.job-opening.delete')->middleware('hasPermission:cms_delete');

    Route::get('cms/content-blocks/manage', [ContentBlockController::class, 'index'])->name('cms.content-block.index')->middleware('hasPermission:cms_read');
    Route::get('cms/content-blocks/manage/create', [ContentBlockController::class, 'create'])->name('cms.content-block.create')->middleware('hasPermission:cms_create');
    Route::post('cms/content-blocks/manage/store', [ContentBlockController::class, 'store'])->name('cms.content-block.store')->middleware('hasPermission:cms_create');
    Route::get('cms/content-blocks/manage/{id}/edit', [ContentBlockController::class, 'edit'])->name('cms.content-block.edit')->middleware('hasPermission:cms_update');
    Route::put('cms/content-blocks/manage/update', [ContentBlockController::class, 'update'])->name('cms.content-block.update')->middleware('hasPermission:cms_update');
    Route::delete('cms/content-blocks/manage/{id}/delete', [ContentBlockController::class, 'delete'])->name('cms.content-block.delete')->middleware('hasPermission:cms_delete');

    Route::get('cms/seo-settings', [CmsController::class, 'seo'])->name('cms.seo')->middleware('hasPermission:cms_read');
    Route::put('cms/seo-settings', [CmsController::class, 'seoUpdate'])->name('cms.seo.update')->middleware('hasPermission:cms_update');

    // Customer Portal
    Route::get('portal/customer/dashboard', [CustomerPortalController::class, 'dashboard'])->name('cust.dashboard')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/trip-planner', [GrowthFeatureController::class, 'planner'])->name('cust.trip-planner')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/trip-planner', [GrowthFeatureController::class, 'generate'])->middleware(['hasPermission:customer_portal_read','throttle:10,1'])->name('cust.trip-planner.generate');
    Route::get('portal/customer/loyalty', [GrowthFeatureController::class, 'loyalty'])->name('cust.loyalty')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/passport', [CustomerPortalController::class, 'passport'])->name('cust.passport')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/travelers', [CustomerPortalController::class, 'travelers'])->name('cust.travelers')->middleware('hasPermission:customer_portal_read');
    // Customer self-service: Passports (ownership scoped in the repository)
    Route::get('portal/customer/passport/create', [CustomerPortalController::class, 'passportCreate'])->name('cust.passport.create')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/passport/store', [CustomerPortalController::class, 'passportStore'])->name('cust.passport.store')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/passport/{id}/edit', [CustomerPortalController::class, 'passportEdit'])->name('cust.passport.edit')->middleware('hasPermission:customer_portal_read');
    Route::put('portal/customer/passport/update', [CustomerPortalController::class, 'passportUpdate'])->name('cust.passport.update')->middleware('hasPermission:customer_portal_read');
    Route::delete('portal/customer/passport/{id}/delete', [CustomerPortalController::class, 'passportDelete'])->name('cust.passport.delete')->middleware('hasPermission:customer_portal_read');
    // Customer self-service: Travelers (ownership scoped in the repository)
    Route::get('portal/customer/travelers/create', [CustomerPortalController::class, 'travelersCreate'])->name('cust.travelers.create')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/travelers/store', [CustomerPortalController::class, 'travelersStore'])->name('cust.travelers.store')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/travelers/{id}/edit', [CustomerPortalController::class, 'travelersEdit'])->name('cust.travelers.edit')->middleware('hasPermission:customer_portal_read');
    Route::put('portal/customer/travelers/update', [CustomerPortalController::class, 'travelersUpdate'])->name('cust.travelers.update')->middleware('hasPermission:customer_portal_read');
    Route::delete('portal/customer/travelers/{id}/delete', [CustomerPortalController::class, 'travelersDelete'])->name('cust.travelers.delete')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/bookings', [CustomerPortalController::class, 'bookings'])->name('cust.bookings')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/tour-bookings', [CustomerPortalController::class, 'tours'])->name('cust.tours')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/visa-applications', [CustomerPortalController::class, 'visa'])->name('cust.visa')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/visa-applications/documents/{id}/download', [CustomerPortalController::class, 'downloadVisaDocument'])->name('cust.visa.document.download')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/flight-tickets', [CustomerPortalController::class, 'flights'])->name('cust.flights')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/hotel-bookings', [CustomerPortalController::class, 'hotels'])->name('cust.hotels')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/transport-bookings', [CustomerPortalController::class, 'transport'])->name('cust.transport')->middleware('hasPermission:customer_portal_read');
    // Customer self-service: booking & payment (ownership scoped in the repository)
    Route::post('portal/customer/tour-bookings/book', [CustomerPortalController::class, 'bookTour'])->name('cust.tours.book')->middleware('hasPermission:customer_portal_read');
    // Live coupon / points quote for the Book modal (JSON).
    Route::post('portal/customer/tour-bookings/quote', [CustomerPortalController::class, 'quoteTour'])->name('cust.tours.quote')->middleware(['hasPermission:customer_portal_read', 'throttle:30,1']);
    Route::post('portal/customer/bookings/pay', [CustomerPortalController::class, 'payBooking'])->name('cust.bookings.pay')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/hotel-bookings/book', [CustomerPortalController::class, 'bookHotel'])->name('cust.hotels.book')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/hotel-bookings/pay', [CustomerPortalController::class, 'payHotel'])->name('cust.hotels.pay')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/transport-bookings/request', [CustomerPortalController::class, 'requestTransport'])->name('cust.transport.book')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/transport-bookings/pay', [CustomerPortalController::class, 'payTransport'])->name('cust.transport.pay')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/invoices', [CustomerPortalController::class, 'invoices'])->name('cust.invoices')->middleware('hasPermission:customer_portal_read');
    // Customer's own paperwork as PDF (ownership enforced in the repository).
    Route::get('portal/customer/documents/{kind}/{id}/pdf', [CustomerPortalController::class, 'documentPdf'])->whereIn('kind', ['invoice', 'receipt', 'hotel'])->name('cust.document.pdf')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/payments', [CustomerPortalController::class, 'payments'])->name('cust.payments')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/wallet', [CustomerPortalController::class, 'wallet'])->name('cust.wallet')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/documents', [CustomerPortalController::class, 'documents'])->name('cust.documents')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/support-tickets', [CustomerPortalController::class, 'support'])->name('cust.support')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/support-tickets/create', [CustomerPortalController::class, 'supportCreate'])->name('cust.support.create')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/support-tickets/store', [CustomerPortalController::class, 'supportStore'])->name('cust.support.store')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/notifications', [NotificationController::class, 'index'])->name('cust.notifications')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/reviews', [CustomerPortalController::class, 'reviews'])->name('cust.reviews')->middleware('hasPermission:customer_portal_read');
    Route::post('portal/customer/reviews/store', [CustomerPortalController::class, 'reviewStore'])->name('cust.reviews.store')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/wishlist', [CustomerPortalController::class, 'wishlist'])->name('cust.wishlist')->middleware('hasPermission:customer_portal_read');
    Route::delete('portal/customer/wishlist/{id}/delete', [CustomerPortalController::class, 'wishlistDelete'])->name('cust.wishlist.delete')->middleware('hasPermission:customer_portal_read');
    Route::get('portal/customer/settings', [CustomerPortalController::class, 'settings'])->name('cust.settings')->middleware('hasPermission:customer_portal_read');
    Route::put('portal/customer/settings', [CustomerPortalController::class, 'settingsUpdate'])->name('cust.settings.update')->middleware('hasPermission:customer_portal_read');

    // Agent Portal
    Route::get('portal/agent/dashboard', [AgentPortalController::class, 'dashboard'])->name('agent.dashboard')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/bookings', [AgentPortalController::class, 'bookings'])->name('agent.bookings')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/bookings/create', [AgentPortalController::class, 'bookingCreate'])->name('agent.booking.create')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/bookings', [AgentPortalController::class, 'bookingStore'])->name('agent.booking.store')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/hotels/create', [AgentPortalController::class, 'hotelCreate'])->name('agent.hotel.create')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/hotels', [AgentPortalController::class, 'hotelStore'])->name('agent.hotel.store')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/transports/create', [AgentPortalController::class, 'transportCreate'])->name('agent.transport.create')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/transports', [AgentPortalController::class, 'transportStore'])->name('agent.transport.store')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/flights/create', [AgentPortalController::class, 'flightCreate'])->name('agent.flight.create')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/flights', [AgentPortalController::class, 'flightStore'])->name('agent.flight.store')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/bookings/{id}', [AgentPortalController::class, 'booking'])->name('agent.booking.show')->middleware('hasPermission:agent_portal_read');
    Route::put('portal/agent/bookings/{id}', [AgentPortalController::class, 'bookingUpdate'])->name('agent.booking.update')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/bookings/{id}/cancel', [AgentPortalController::class, 'bookingCancel'])->name('agent.booking.cancel')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/customers', [AgentPortalController::class, 'customers'])->name('agent.customers')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/visa-documents/{id}/download', [AgentPortalController::class, 'downloadVisaDocument'])->name('agent.visa.document.download')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/commissions', [AgentPortalController::class, 'commissions'])->name('agent.commissions')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/wallet', [AgentPortalController::class, 'wallet'])->name('agent.wallet')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/transactions', [AgentPortalController::class, 'transactions'])->name('agent.transactions')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/invoices', [AgentPortalController::class, 'invoices'])->name('agent.invoices')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/wallet/withdraw', [AgentPortalController::class, 'requestWithdrawal'])->name('agent.wallet.withdraw')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/reports', [AgentPortalController::class, 'reports'])->name('agent.reports')->middleware('hasPermission:agent_portal_read');
    Route::get('portal/agent/support', [AgentPortalController::class, 'support'])->name('agent.support')->middleware('hasPermission:agent_portal_read');
    Route::post('portal/agent/support', [AgentPortalController::class, 'raiseTicket'])->name('agent.support.raise')->middleware('hasPermission:agent_portal_read');

    // Staff Portal
    Route::get('portal/staff/dashboard', [StaffPortalController::class, 'dashboard'])->name('staff.dashboard')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/tasks', [StaffPortalController::class, 'tasks'])->name('staff.tasks')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/attendance', [StaffPortalController::class, 'attendance'])->name('staff.attendance')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/leave', [StaffPortalController::class, 'leave'])->name('staff.leave')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/leave/create', [StaffPortalController::class, 'leaveCreate'])->name('staff.leave.create')->middleware('hasPermission:staff_portal_read');
    Route::post('portal/staff/leave/store', [StaffPortalController::class, 'leaveStore'])->name('staff.leave.store')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/payslips', [StaffPortalController::class, 'payslips'])->name('staff.payslips')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/profile', [StaffPortalController::class, 'profile'])->name('staff.profile')->middleware('hasPermission:staff_portal_read');
    Route::get('portal/staff/profile/edit', [StaffPortalController::class, 'profileEdit'])->name('staff.profile.edit')->middleware('hasPermission:staff_portal_read');
    Route::put('portal/staff/profile/update', [StaffPortalController::class, 'profileUpdate'])->name('staff.profile.update')->middleware('hasPermission:staff_portal_read');

    // Super Admin SaaS routes now live in Modules/Saas/routes/web.php

    // Activity logs
    Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index')->middleware('hasPermission:activity_logs_read');
    Route::get('admin/activity-logs/view/{id}', [ActivityLogController::class, 'view'])->name('activity.logs.view')->middleware('hasPermission:activity_logs_read');

    // Login activity
    Route::get('admin/login-activity/index', [LoginActivityController::class, 'index'])->name('login.activity.index')->middleware('hasPermission:login_activity_read');

    // Languages
    Route::get('app-language/index', [LanguageController::class, 'index'])->name('language.index')->middleware('hasPermission:language_read');
    Route::get('app-language/create', [LanguageController::class, 'create'])->name('language.create')->middleware('hasPermission:language_create');
    Route::post('app-language/store', [LanguageController::class, 'store'])->name('language.store')->middleware('hasPermission:language_create');
    Route::get('app-language/edit/{id}', [LanguageController::class, 'edit'])->name('language.edit')->middleware('hasPermission:language_update');
    Route::put('app-language/update', [LanguageController::class, 'update'])->name('language.update')->middleware(['hasPermission:language_update', 'demo.readonly']);
    Route::delete('app-language/delete/{id}', [LanguageController::class, 'delete'])->name('language.delete')->middleware(['hasPermission:language_delete', 'demo.readonly']);

    Route::get('app-language/edit/phrase/{id}', [LanguageController::class, 'editPhrase'])->name('language.edit.phrase')->middleware('hasPermission:language_phrase_update');
    Route::post('app-language/update/phrase', [LanguageController::class, 'updatePhrase'])->name('language.update.phrase')->middleware(['hasPermission:language_phrase_update', 'demo.readonly']);
    Route::get('app-language/module/phrase', [LanguageController::class, 'modulePhrase'])->name('language.module.phrase');

    // Search
    Route::get('search', [SearchController::class, 'search'])->name('search')->middleware('hasPermission:route_search');
    Route::post('search/routes', [SearchController::class, 'searchRoute'])->name('search.route')->middleware('hasPermission:route_search');

    // ============ New modules: Marketing, Branches & Services ============
    Route::get("coupons", [CouponController::class, "index"])->name("coupon.index")->middleware("hasPermission:coupon_read");
    Route::get("coupons/create", [CouponController::class, "create"])->name("coupon.create")->middleware("hasPermission:coupon_create");
    Route::post("coupons/store", [CouponController::class, "store"])->name("coupon.store")->middleware("hasPermission:coupon_create");
    Route::get("coupons/{id}/edit", [CouponController::class, "edit"])->name("coupon.edit")->middleware("hasPermission:coupon_update");
    Route::put("coupons/update", [CouponController::class, "update"])->name("coupon.update")->middleware("hasPermission:coupon_update");
    Route::delete("coupons/{id}/delete", [CouponController::class, "delete"])->name("coupon.delete")->middleware("hasPermission:coupon_delete");

    // ===== Reviews (moderation only — customers write them, staff publish them) =====
    Route::get("reviews", [ReviewController::class, "index"])->name("review.index")->middleware("hasPermission:review_read");
    Route::get("reviews/{id}/edit", [ReviewController::class, "edit"])->name("review.edit")->middleware("hasPermission:review_update");
    Route::put("reviews/update", [ReviewController::class, "update"])->name("review.update")->middleware("hasPermission:review_update");
    Route::post("reviews/{id}/approve", [ReviewController::class, "approve"])->name("review.approve")->middleware("hasPermission:review_update");
    Route::post("reviews/{id}/reject", [ReviewController::class, "reject"])->name("review.reject")->middleware("hasPermission:review_update");
    Route::delete("reviews/{id}/delete", [ReviewController::class, "delete"])->name("review.delete")->middleware("hasPermission:review_delete");

    Route::get("campaigns", [CampaignController::class, "index"])->name("campaign.index")->middleware("hasPermission:campaign_read");
    Route::get("campaigns/create", [CampaignController::class, "create"])->name("campaign.create")->middleware("hasPermission:campaign_create");
    Route::post("campaigns/store", [CampaignController::class, "store"])->name("campaign.store")->middleware("hasPermission:campaign_create");
    Route::get("campaigns/{id}/edit", [CampaignController::class, "edit"])->name("campaign.edit")->middleware("hasPermission:campaign_update");
    Route::put("campaigns/update", [CampaignController::class, "update"])->name("campaign.update")->middleware("hasPermission:campaign_update");
    Route::delete("campaigns/{id}/delete", [CampaignController::class, "delete"])->name("campaign.delete")->middleware("hasPermission:campaign_delete");
    Route::get("marketing/newsletter-subscribers", [NewsletterSubscriberController::class, "index"])->name("newsletter-subscriber.index")->middleware("hasPermission:campaign_read");
    Route::delete("marketing/newsletter-subscribers/{id}/delete", [NewsletterSubscriberController::class, "delete"])->name("newsletter-subscriber.delete")->middleware("hasPermission:campaign_delete");

    Route::get("branches", [BranchController::class, "index"])->name("branch.index")->middleware("hasPermission:branch_read");
    Route::get("branches/create", [BranchController::class, "create"])->name("branch.create")->middleware("hasPermission:branch_create");
    Route::post("branches/store", [BranchController::class, "store"])->name("branch.store")->middleware("hasPermission:branch_create");
    Route::get("branches/{id}/edit", [BranchController::class, "edit"])->name("branch.edit")->middleware("hasPermission:branch_update");
    Route::put("branches/update", [BranchController::class, "update"])->name("branch.update")->middleware("hasPermission:branch_update");
    Route::delete("branches/{id}/delete", [BranchController::class, "delete"])->name("branch.delete")->middleware("hasPermission:branch_delete");

    Route::get("insurances", [InsuranceController::class, "index"])->name("insurance.index")->middleware("hasPermission:insurance_read");
    Route::get("insurances/create", [InsuranceController::class, "create"])->name("insurance.create")->middleware("hasPermission:insurance_create");
    Route::post("insurances/store", [InsuranceController::class, "store"])->name("insurance.store")->middleware("hasPermission:insurance_create");
    Route::get("insurances/{id}/edit", [InsuranceController::class, "edit"])->name("insurance.edit")->middleware("hasPermission:insurance_update");
    Route::put("insurances/update", [InsuranceController::class, "update"])->name("insurance.update")->middleware("hasPermission:insurance_update");
    Route::delete("insurances/{id}/delete", [InsuranceController::class, "delete"])->name("insurance.delete")->middleware("hasPermission:insurance_delete");

    Route::get("student-services", [StudentServiceController::class, "index"])->name("student-service.index")->middleware("hasPermission:student_service_read");
    Route::get("student-services/create", [StudentServiceController::class, "create"])->name("student-service.create")->middleware("hasPermission:student_service_create");
    Route::post("student-services/store", [StudentServiceController::class, "store"])->name("student-service.store")->middleware("hasPermission:student_service_create");
    Route::get("student-services/{id}/edit", [StudentServiceController::class, "edit"])->name("student-service.edit")->middleware("hasPermission:student_service_update");
    Route::put("student-services/update", [StudentServiceController::class, "update"])->name("student-service.update")->middleware("hasPermission:student_service_update");
    Route::delete("student-services/{id}/delete", [StudentServiceController::class, "delete"])->name("student-service.delete")->middleware("hasPermission:student_service_delete");

    Route::get("medical-tours", [MedicalTourController::class, "index"])->name("medical-tour.index")->middleware("hasPermission:medical_tour_read");
    Route::get("medical-tours/create", [MedicalTourController::class, "create"])->name("medical-tour.create")->middleware("hasPermission:medical_tour_create");
    Route::post("medical-tours/store", [MedicalTourController::class, "store"])->name("medical-tour.store")->middleware("hasPermission:medical_tour_create");
    Route::get("medical-tours/{id}/edit", [MedicalTourController::class, "edit"])->name("medical-tour.edit")->middleware("hasPermission:medical_tour_update");
    Route::put("medical-tours/update", [MedicalTourController::class, "update"])->name("medical-tour.update")->middleware("hasPermission:medical_tour_update");
    Route::delete("medical-tours/{id}/delete", [MedicalTourController::class, "delete"])->name("medical-tour.delete")->middleware("hasPermission:medical_tour_delete");

    Route::get("corporate-travels", [CorporateTravelController::class, "index"])->name("corporate-travel.index")->middleware("hasPermission:corporate_travel_read");
    Route::get("corporate-travels/create", [CorporateTravelController::class, "create"])->name("corporate-travel.create")->middleware("hasPermission:corporate_travel_create");
    Route::post("corporate-travels/store", [CorporateTravelController::class, "store"])->name("corporate-travel.store")->middleware("hasPermission:corporate_travel_create");
    Route::get("corporate-travels/{id}/edit", [CorporateTravelController::class, "edit"])->name("corporate-travel.edit")->middleware("hasPermission:corporate_travel_update");
    Route::put("corporate-travels/update", [CorporateTravelController::class, "update"])->name("corporate-travel.update")->middleware("hasPermission:corporate_travel_update");
    Route::delete("corporate-travels/{id}/delete", [CorporateTravelController::class, "delete"])->name("corporate-travel.delete")->middleware("hasPermission:corporate_travel_delete");

    Route::get("event-tours", [EventTourController::class, "index"])->name("event-tour.index")->middleware("hasPermission:event_tour_read");
    Route::get("event-tours/create", [EventTourController::class, "create"])->name("event-tour.create")->middleware("hasPermission:event_tour_create");
    Route::post("event-tours/store", [EventTourController::class, "store"])->name("event-tour.store")->middleware("hasPermission:event_tour_create");
    Route::get("event-tours/{id}/edit", [EventTourController::class, "edit"])->name("event-tour.edit")->middleware("hasPermission:event_tour_update");
    Route::put("event-tours/update", [EventTourController::class, "update"])->name("event-tour.update")->middleware("hasPermission:event_tour_update");
    Route::delete("event-tours/{id}/delete", [EventTourController::class, "delete"])->name("event-tour.delete")->middleware("hasPermission:event_tour_delete");

    // Event Bookings CRUD (sub-entity, reuses event_tour_* permissions)
    Route::get("event-tours/bookings/manage", [EventBookingController::class, "index"])->name("event-tour.booking.index")->middleware("hasPermission:event_tour_read");
    Route::get("event-tours/bookings/manage/create", [EventBookingController::class, "create"])->name("event-tour.booking.create")->middleware("hasPermission:event_tour_create");
    Route::post("event-tours/bookings/manage/store", [EventBookingController::class, "store"])->name("event-tour.booking.store")->middleware("hasPermission:event_tour_create");
    Route::get("event-tours/bookings/manage/{id}/edit", [EventBookingController::class, "edit"])->name("event-tour.booking.edit")->middleware("hasPermission:event_tour_update");
    Route::put("event-tours/bookings/manage/update", [EventBookingController::class, "update"])->name("event-tour.booking.update")->middleware("hasPermission:event_tour_update");
    Route::delete("event-tours/bookings/manage/{id}/delete", [EventBookingController::class, "delete"])->name("event-tour.booking.delete")->middleware("hasPermission:event_tour_delete");

});
