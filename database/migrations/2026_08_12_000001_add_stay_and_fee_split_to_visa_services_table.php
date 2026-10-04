<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The catalogue advertised only a processing time and one lump fee, so the
     * two questions every applicant asks — "how long can I stay?" and "is the
     * embassy fee included?" — had no home in the data.
     */
    public function up(): void
    {
        Schema::table('visa_services', function (Blueprint $table) {
            $table->string('stay_duration')->nullable()->after('processing_time'); // e.g. "30 days"
            $table->string('entry_type')->default('Single')->after('stay_duration'); // Single | Multiple
            $table->decimal('govt_fee', 12, 2)->default(0)->after('entry_type');   // embassy / VFS, passed through
            $table->decimal('service_fee', 12, 2)->default(0)->after('govt_fee');  // what the agency keeps
        });

        // Existing rows advertised `fee` as the all-in price. Treat it as the
        // government portion so the published total never changes on deploy;
        // staff can split it properly when they next edit the service.
        DB::table('visa_services')->update(['govt_fee' => DB::raw('fee')]);
    }

    public function down(): void
    {
        Schema::table('visa_services', function (Blueprint $table) {
            $table->dropColumn(['stay_duration', 'entry_type', 'govt_fee', 'service_fee']);
        });
    }
};
