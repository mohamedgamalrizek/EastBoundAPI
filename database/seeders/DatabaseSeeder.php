<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\UploadSeeder;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---- Structural / bootstrap data — always seeded, demo or not. ----
        // The app cannot function without these: permissions, roles, login
        // accounts, and reference data used throughout every form.
        $this->call([
            RouteListSeeder::class,
            CurrencySeeder::class,
            LanguageSeeder::class,
            FlagIconSeeder::class,

            SettingSeeder::class,
            SocialLinkSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,

            // Public-site navigation and the reusable content cards are
            // structural, not demo content — without them the marketing site
            // renders no menus and several empty sections.
            MenuSeeder::class,
            ContentBlockSeeder::class,
        ]);

        // SaaS module — Plans (real product data) always seed when SaaS mode
        // is on; SaasDatabaseSeeder itself gates the demo Tenant rows behind
        // APP_DEMO, so this must run before the early-return below.
        if (config('saas.enabled')) {
            $this->call(\Modules\Saas\Database\Seeders\SaasDatabaseSeeder::class);
        }

        // Demo login accounts ride with the sample dataset: APP_DEMO is the
        // single switch for everything a real install must not receive.
        if (config('app.demo')) {
            $this->call(UserSeeder::class);
        }

        // ---- Sample content — only seeded when APP_DEMO=true. ----
        if (! config('app.demo')) {
            return;
        }

        $this->call(TodoSeeder::class);
        $this->call(PackageCategorySeeder::class); // before PackageSeeder: packages.category_id links here
        $this->call(PackageSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(BookingSeeder::class);

        // Travel ERP module demo data
        //
        // Catalogues first: an application links to the visa service it is
        // for, a transport booking to a driver, and an activity to its lead —
        // seeding the record before the thing it points at left those columns
        // NULL and the linked screens empty.
        $this->call([
            VisaServiceSeeder::class,
            DriverSeeder::class,
            LeadSeeder::class,
        ]);

        $this->call([
            VisaApplicationSeeder::class,
            VisaDocumentSeeder::class,
            HajjPackageSeeder::class,
            // Groups + flights first: the pilgrims are placed into them.
            HajjGroupSeeder::class,
            HajjPilgrimSeeder::class,
            HotelSeeder::class,
            TransportBookingSeeder::class,
            FlightBookingSeeder::class,
            AccountSeeder::class,
            AccountTransactionSeeder::class,
            SupplierSeeder::class,
            SupplierContractSeeder::class,
            TaskSeeder::class,
            SupportTicketSeeder::class,
            CmsPageSeeder::class,
            BlogSeeder::class,
            CrmActivitySeeder::class,
            TourScheduleSeeder::class,
            TourGuideSeeder::class,
            // residual sub-entities
            HotelRoomSeeder::class,
            HotelBookingSeeder::class,
            KbArticleSeeder::class,
            AnnouncementSeeder::class,
            GallerySeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            SliderSeeder::class,
            // Public-website catalogues (rendered by the marketing site)
            FlightRouteSeeder::class,
            TransportServiceSeeder::class,
            VehicleCategorySeeder::class,
            JobOpeningSeeder::class,
            InvoiceSeeder::class,
            ReceiptSeeder::class,
        ]);

        // CRM, marketing & organisation demo data
        $this->call([
            ContactMessageSeeder::class,
            CampaignSeeder::class,
            CouponSeeder::class,
            SubscriberSeeder::class,
            BranchSeeder::class,
            // Service verticals
            InsuranceSeeder::class,
            StudentServiceSeeder::class,
            MedicalTourSeeder::class,
            CorporateTravelSeeder::class,
            EventTourSeeder::class,
            EventBookingSeeder::class, // after EventTourSeeder: bookings link to its events
            // needs JobOpeningSeeder (above) to have run first
            JobApplicationSeeder::class,
        ]);

        // Customer & Agent portal demo data
        $this->call([
            TravelerSeeder::class,
            PassportSeeder::class,
            CustomerDocumentSeeder::class,
            WalletTransactionSeeder::class,
            NotificationSeeder::class,
            // Order is the settlement chain itself: commissions are earned
            // from paid bookings and approved into the wallet, payouts then
            // draw on that wallet, and the wallet seeder recomputes the
            // running balance from both.
            AgentCommissionSeeder::class,
            AgentWithdrawalSeeder::class,
            AgentWalletTransactionSeeder::class,
            AgentInvoiceSeeder::class,
            // Staff portal
            StaffAttendanceSeeder::class,
            LeaveRequestSeeder::class,
            PayslipSeeder::class,
        ]);

        // Close the books: re-post every document that belongs in the journal
        // (invoices, receipts, payslips, agent commissions and payouts) and
        // re-derive balances and agent wallets, so a freshly seeded database
        // opens with a trial balance that actually balances.
        app(\App\Services\Accounting\LedgerService::class)->rebuildAll();
    }
}
