<?php

namespace App\Providers;

use App\Interfaces\AuthInterface;
use App\Repositories\AuthRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Role\RoleInterface;
use App\Repositories\Todo\TodoInterface;
use App\Repositories\User\UserInterface;
use App\Repositories\Role\RoleRepository;
use App\Repositories\Todo\TodoRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Package\PackageInterface;
use App\Repositories\Package\PackageRepository;
use App\Repositories\Booking\BookingInterface;
use App\Repositories\Booking\BookingRepository;
use App\Repositories\Customer\CustomerInterface;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\Upload\UploadInterface;
use App\Repositories\Upload\UploadRepository;
use App\Repositories\Language\LanguageInterface;
use App\Repositories\Settings\SettingsInterface;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Settings\SettingsRepository;
use App\Repositories\LoginActivity\LoginActivityInterface;
use App\Repositories\LoginActivity\LoginActivityRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoginActivityInterface::class,         LoginActivityRepository::class);

        $this->app->bind(SettingsInterface::class,              SettingsRepository::class);

        $this->app->bind(LanguageInterface::class,              LanguageRepository::class);

        $this->app->bind(UploadInterface::class,                UploadRepository::class);

        $this->app->bind(AuthInterface::class,                  AuthRepository::class);

        $this->app->bind(UserInterface::class,                  UserRepository::class);

        $this->app->bind(RoleInterface::class,                  RoleRepository::class);

        $this->app->bind(TodoInterface::class,                  TodoRepository::class);

        $this->app->bind(PackageInterface::class,               PackageRepository::class);

        $this->app->bind(BookingInterface::class,               BookingRepository::class);

        $this->app->bind(CustomerInterface::class,              CustomerRepository::class);

        $this->app->bind(\App\Repositories\Lead\LeadInterface::class, \App\Repositories\Lead\LeadRepository::class);
        $this->app->bind(\App\Repositories\ContactMessage\ContactMessageInterface::class, \App\Repositories\ContactMessage\ContactMessageRepository::class);

        $this->app->bind(\App\Repositories\Crm\CrmInterface::class, \App\Repositories\Crm\CrmRepository::class);
        $this->app->bind(\App\Repositories\Visa\VisaInterface::class, \App\Repositories\Visa\VisaRepository::class);
        $this->app->bind(\App\Repositories\Tour\TourInterface::class, \App\Repositories\Tour\TourRepository::class);
        $this->app->bind(\App\Repositories\Hajj\HajjInterface::class, \App\Repositories\Hajj\HajjRepository::class);
        $this->app->bind(\App\Repositories\Hotel\HotelInterface::class, \App\Repositories\Hotel\HotelRepository::class);
        $this->app->bind(\App\Repositories\Transport\TransportInterface::class, \App\Repositories\Transport\TransportRepository::class);
        $this->app->bind(\App\Repositories\Flight\FlightInterface::class, \App\Repositories\Flight\FlightRepository::class);
        $this->app->bind(\App\Repositories\Accounting\AccountingInterface::class, \App\Repositories\Accounting\AccountingRepository::class);
        $this->app->bind(\App\Repositories\Supplier\SupplierInterface::class, \App\Repositories\Supplier\SupplierRepository::class);
        $this->app->bind(\App\Repositories\Task\TaskInterface::class, \App\Repositories\Task\TaskRepository::class);
        $this->app->bind(\App\Repositories\Support\SupportInterface::class, \App\Repositories\Support\SupportRepository::class);
        $this->app->bind(\App\Repositories\Report\ReportInterface::class, \App\Repositories\Report\ReportRepository::class);
        $this->app->bind(\App\Repositories\Cms\CmsInterface::class, \App\Repositories\Cms\CmsRepository::class);
        $this->app->bind(\App\Repositories\CustomerPortal\CustomerPortalInterface::class, \App\Repositories\CustomerPortal\CustomerPortalRepository::class);
        $this->app->bind(\App\Repositories\AgentPortal\AgentPortalInterface::class, \App\Repositories\AgentPortal\AgentPortalRepository::class);
        $this->app->bind(\App\Repositories\StaffPortal\StaffPortalInterface::class, \App\Repositories\StaffPortal\StaffPortalRepository::class);
        // ---- Sub-entities (reuse parent-module permissions) ----
        $this->app->bind(\App\Repositories\HajjPilgrim\HajjPilgrimInterface::class, \App\Repositories\HajjPilgrim\HajjPilgrimRepository::class);
        $this->app->bind(\App\Repositories\HotelRoom\HotelRoomInterface::class, \App\Repositories\HotelRoom\HotelRoomRepository::class);
        $this->app->bind(\App\Repositories\HotelBooking\HotelBookingInterface::class, \App\Repositories\HotelBooking\HotelBookingRepository::class);
        $this->app->bind(\App\Repositories\Driver\DriverInterface::class, \App\Repositories\Driver\DriverRepository::class);
        $this->app->bind(\App\Repositories\VehicleCategory\VehicleCategoryInterface::class, \App\Repositories\VehicleCategory\VehicleCategoryRepository::class);
        $this->app->bind(\App\Repositories\AccountTransaction\AccountTransactionInterface::class, \App\Repositories\AccountTransaction\AccountTransactionRepository::class);
        $this->app->bind(\App\Repositories\Blog\BlogInterface::class, \App\Repositories\Blog\BlogRepository::class);
        $this->app->bind(\App\Repositories\Slider\SliderInterface::class, \App\Repositories\Slider\SliderRepository::class);
        $this->app->bind(\App\Repositories\Testimonial\TestimonialInterface::class, \App\Repositories\Testimonial\TestimonialRepository::class);
        $this->app->bind(\App\Repositories\Announcement\AnnouncementInterface::class, \App\Repositories\Announcement\AnnouncementRepository::class);
        $this->app->bind(\App\Repositories\Gallery\GalleryInterface::class, \App\Repositories\Gallery\GalleryRepository::class);
        $this->app->bind(\App\Repositories\Faq\FaqInterface::class, \App\Repositories\Faq\FaqRepository::class);
        $this->app->bind(\App\Repositories\Menu\MenuInterface::class, \App\Repositories\Menu\MenuRepository::class);
        // ---- Public-website catalogues (CMS) ----
        $this->app->bind(\App\Repositories\VisaService\VisaServiceInterface::class, \App\Repositories\VisaService\VisaServiceRepository::class);
        $this->app->bind(\App\Repositories\FlightRoute\FlightRouteInterface::class, \App\Repositories\FlightRoute\FlightRouteRepository::class);
        $this->app->bind(\App\Repositories\TransportService\TransportServiceInterface::class, \App\Repositories\TransportService\TransportServiceRepository::class);
        $this->app->bind(\App\Repositories\JobOpening\JobOpeningInterface::class, \App\Repositories\JobOpening\JobOpeningRepository::class);
        $this->app->bind(\App\Repositories\ContentBlock\ContentBlockInterface::class, \App\Repositories\ContentBlock\ContentBlockRepository::class);
        // ---- New modules (Marketing, Branches, Services) ----
        $this->app->bind(\App\Repositories\Coupon\CouponInterface::class, \App\Repositories\Coupon\CouponRepository::class);
        $this->app->bind(\App\Repositories\Review\ReviewInterface::class, \App\Repositories\Review\ReviewRepository::class);
        $this->app->bind(\App\Repositories\Campaign\CampaignInterface::class, \App\Repositories\Campaign\CampaignRepository::class);
        $this->app->bind(\App\Repositories\Branch\BranchInterface::class, \App\Repositories\Branch\BranchRepository::class);
        $this->app->bind(\App\Repositories\Insurance\InsuranceInterface::class, \App\Repositories\Insurance\InsuranceRepository::class);
        $this->app->bind(\App\Repositories\StudentService\StudentServiceInterface::class, \App\Repositories\StudentService\StudentServiceRepository::class);
        $this->app->bind(\App\Repositories\MedicalTour\MedicalTourInterface::class, \App\Repositories\MedicalTour\MedicalTourRepository::class);
        $this->app->bind(\App\Repositories\CorporateTravel\CorporateTravelInterface::class, \App\Repositories\CorporateTravel\CorporateTravelRepository::class);
        $this->app->bind(\App\Repositories\EventTour\EventTourInterface::class, \App\Repositories\EventTour\EventTourRepository::class);
        $this->app->bind(\App\Repositories\EventBooking\EventBookingInterface::class, \App\Repositories\EventBooking\EventBookingRepository::class);

        $this->app->bind(\App\Repositories\Invoice\InvoiceInterface::class, \App\Repositories\Invoice\InvoiceRepository::class);
        $this->app->bind(\App\Repositories\Receipt\ReceiptInterface::class, \App\Repositories\Receipt\ReceiptRepository::class);
        $this->app->bind(\App\Repositories\KbArticle\KbArticleInterface::class, \App\Repositories\KbArticle\KbArticleRepository::class);
        $this->app->bind(\App\Repositories\CrmActivity\CrmActivityInterface::class, \App\Repositories\CrmActivity\CrmActivityRepository::class);
        $this->app->bind(\App\Repositories\TourSchedule\TourScheduleInterface::class, \App\Repositories\TourSchedule\TourScheduleRepository::class);
        // ---- Back-office (HR + Agent finance + customer records) ----
        $this->app->bind(\App\Repositories\StaffAttendance\StaffAttendanceInterface::class, \App\Repositories\StaffAttendance\StaffAttendanceRepository::class);
        $this->app->bind(\App\Repositories\LeaveRequest\LeaveRequestInterface::class, \App\Repositories\LeaveRequest\LeaveRequestRepository::class);
        $this->app->bind(\App\Repositories\Payslip\PayslipInterface::class, \App\Repositories\Payslip\PayslipRepository::class);
        $this->app->bind(\App\Repositories\AgentCommission\AgentCommissionInterface::class, \App\Repositories\AgentCommission\AgentCommissionRepository::class);
        $this->app->bind(\App\Repositories\AgentInvoice\AgentInvoiceInterface::class, \App\Repositories\AgentInvoice\AgentInvoiceRepository::class);
        $this->app->bind(\App\Repositories\AgentWithdrawal\AgentWithdrawalInterface::class, \App\Repositories\AgentWithdrawal\AgentWithdrawalRepository::class);
        $this->app->bind(\App\Repositories\Traveler\TravelerInterface::class, \App\Repositories\Traveler\TravelerRepository::class);
        $this->app->bind(\App\Repositories\Passport\PassportInterface::class, \App\Repositories\Passport\PassportRepository::class);
        // SaaS now lives in the Modules/Saas module (self-contained, no central binding).
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
