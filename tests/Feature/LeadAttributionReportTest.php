<?php

namespace Tests\Feature;

use App\Repositories\Report\ReportRepository;
use App\Services\Reports\LeadAttributionReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadAttributionReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'lead_reports', 'database.connections.lead_reports' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('website')->nullable();
            $table->string('language')->nullable();
            $table->string('stage')->default('New');
            $table->decimal('value', 12, 2)->default(0);
            $table->timestamps();
        });
        foreach (['bookings', 'flight_bookings', 'hotel_bookings'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('fare', 12, 2)->default(0);
                $table->string('status');
                $table->timestamps();
            });
        }

        $migration = require database_path('migrations/2026_10_23_000001_link_bookings_to_leads.php');
        $migration->up();
        config(['lead-reporting.websites' => ['one.test', 'two.test', 'three.test', 'four.test', 'five.test', 'six.test']]);
    }

    protected function tearDown(): void
    {
        DB::purge('lead_reports');
        parent::tearDown();
    }

    public function test_all_six_websites_and_four_languages_are_visible_without_activity(): void
    {
        $report = app(LeadAttributionReport::class)->build();

        $this->assertCount(6, $report['byWebsite']);
        $this->assertCount(4, $report['byLanguage']);
        $this->assertCount(24, $report['matrix']);
        $this->assertEquals(0, $report['totals']['leads']);
        $this->assertEquals(0, $report['totals']['revenue']);
    }

    public function test_revenue_uses_real_bookings_without_duplicating_package_flights(): void
    {
        $english = $this->lead('one.test', 'English', 'Won', 9000);
        $french = $this->lead('two.test', 'French');
        $package = $this->sale('bookings', $english, 120, 'paid');
        $this->sale('bookings', $english, 500, 'cancelled');
        $this->sale('flight_bookings', $english, 999, 'Confirmed', $package);
        $this->sale('flight_bookings', $english, 40, 'Confirmed');
        $this->sale('flight_bookings', $english, 30, 'Reissued');
        $this->sale('flight_bookings', $english, 600, 'Refunded');
        $this->sale('flight_bookings', $english, 700, 'Pending');
        $this->sale('hotel_bookings', $french, 80, 'Confirmed');
        $this->sale('hotel_bookings', $french, 20, 'Paid');
        $this->sale('hotel_bookings', $french, 300, 'Booked');
        $this->sale('hotel_bookings', $french, 400, 'Cancelled');
        $this->sale('bookings', null, 90, 'confirmed');

        $report = app(LeadAttributionReport::class)->build();

        $this->assertEquals(2, $report['totals']['leads']);
        $this->assertEquals(1, $report['totals']['won']);
        $this->assertEquals(9000, $report['totals']['won_value']);
        $this->assertEquals(5, $report['totals']['bookings']);
        $this->assertEquals(290, $report['totals']['revenue']);
        $this->assertEquals(190, $report['byLanguage']->firstWhere('name', 'English')['revenue']);
        $this->assertEquals(100, $report['byWebsite']->firstWhere('name', 'two.test')['revenue']);
        $this->assertEquals(90, $report['unattributedRevenue']);
    }

    public function test_missing_and_legacy_dimensions_are_visible_without_guessing_language_from_website(): void
    {
        $lead = $this->lead(' English ', null);
        $this->sale('bookings', $lead, 25, 'confirmed');
        $this->lead(' ', ' french ');
        $report = app(LeadAttributionReport::class)->build();

        $row = $report['matrix']->first(fn ($row) => $row->website === 'English' && $row->language === 'Unspecified');
        $this->assertSame(1, $row->leads);
        $this->assertEquals(25, $row->revenue);
        $this->assertEquals(0, $report['byLanguage']->firstWhere('name', 'English')['leads']);
        $this->assertEquals(1, $report['byLanguage']->firstWhere('name', 'French')['leads']);
        $this->assertEquals(1, $report['byWebsite']->firstWhere('name', 'Unspecified')['leads']);
    }

    public function test_dates_apply_to_lead_intake_and_booking_revenue_independently(): void
    {
        $oldLead = $this->lead('one.test', 'English', createdAt: '2025-12-01 12:00:00');
        $this->lead('one.test', 'English');
        $this->sale('bookings', $oldLead, 110, 'confirmed');
        $this->sale('bookings', $oldLead, 999, 'confirmed', createdAt: '2026-02-01 00:00:00');

        $report = app(LeadAttributionReport::class)->build(['from' => '2026-01-01', 'to' => '2026-01-31']);

        $this->assertEquals(1, $report['totals']['leads']);
        $this->assertEquals(110, $report['totals']['revenue']);
    }

    public function test_website_and_language_filters_apply_to_counts_revenue_and_csv(): void
    {
        $lead = $this->lead('one.test', 'English', 'Won', 500);
        $this->lead('one.test', 'French');
        $other = $this->lead('two.test', 'English');
        $this->sale('bookings', $lead, 125.50, 'confirmed');
        $this->sale('bookings', $other, 900, 'confirmed');
        $filters = ['website' => 'one.test', 'language' => 'English'];

        $report = app(LeadAttributionReport::class)->build($filters);
        $export = app(ReportRepository::class)->export('leads', $filters);

        $this->assertCount(1, $report['matrix']);
        $this->assertEquals(1, $report['totals']['leads']);
        $this->assertEquals(125.50, $report['totals']['revenue']);
        $this->assertCount(1, $export['rows']);
        $this->assertEquals(['one.test', 'English', 1, 1, 500, 500, 1, 125.50], $export['rows'][0]);
    }

    public function test_deleted_lead_leaves_booking_as_unattributed(): void
    {
        $lead = $this->lead('one.test', 'English');
        $this->sale('bookings', $lead, 200, 'paid');
        DB::table('leads')->where('id', $lead)->delete();

        $report = app(LeadAttributionReport::class)->build();

        $this->assertEquals(0, $report['totals']['revenue']);
        $this->assertEquals(200, $report['unattributedRevenue']);
    }

    private function lead(?string $website, ?string $language, string $stage = 'New', float $value = 0, string $createdAt = '2026-01-15 12:00:00'): int
    {
        return DB::table('leads')->insertGetId([
            'website' => $website, 'language' => $language, 'stage' => $stage,
            'value' => $value, 'created_at' => $createdAt,
        ]);
    }

    private function sale(string $table, ?int $lead, float $amount, string $status, ?int $bookingId = null, string $createdAt = '2026-01-20 12:00:00'): int
    {
        return DB::table($table)->insertGetId([
            'lead_id' => $lead, 'amount' => $amount, 'fare' => $amount,
            'status' => $status, 'booking_id' => $bookingId, 'created_at' => $createdAt,
        ]);
    }
}
