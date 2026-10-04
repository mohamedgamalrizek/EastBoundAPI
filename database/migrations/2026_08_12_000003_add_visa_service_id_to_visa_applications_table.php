<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Applications recorded the destination and visa type as loose strings, so
     * there was no way back to the catalogue entry that was sold — and no way
     * to report how much a country's visa service actually earned.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('visa_applications', 'visa_service_id')) {
            Schema::table('visa_applications', function (Blueprint $table) {
                $table->unsignedBigInteger('visa_service_id')->nullable()->after('package_id')->index();
            });
        }

        if (! Schema::hasColumn('visa_applications', 'govt_fee')) {
            Schema::table('visa_applications', function (Blueprint $table) {
                $table->decimal('govt_fee', 12, 2)->nullable()->after('visa_type');
            });
        }

        if (! Schema::hasColumn('visa_applications', 'service_fee')) {
            Schema::table('visa_applications', function (Blueprint $table) {
                $table->decimal('service_fee', 12, 2)->nullable()->after('govt_fee');
            });
        }

        // Back-link historical rows where country + type identify exactly one
        // catalogue entry; ambiguous matches stay null for staff to resolve.
        foreach (DB::table('visa_services')->get() as $service) {
            $twin = DB::table('visa_services')
                ->where('country', $service->country)
                ->where('visa_type', $service->visa_type)
                ->count();

            if ($twin !== 1) {
                continue;
            }

            DB::table('visa_applications')
                ->whereNull('visa_service_id')
                ->where('country', $service->country)
                ->where('visa_type', $service->visa_type)
                ->update([
                    'visa_service_id' => $service->id,
                    'govt_fee'        => $service->govt_fee ?? 0,
                    'service_fee'     => $service->service_fee ?? 0,
                ]);
        }
    }

    public function down(): void
    {
        // These columns are part of the base visa applications table for fresh
        // installs. This migration only fills gaps on older databases.
    }
};
