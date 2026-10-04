<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->string('embassy_center')->nullable()->after('appointment_date');
            $table->time('appointment_time')->nullable()->after('embassy_center');
            $table->string('appointment_status')->nullable()->after('appointment_time');
            $table->text('appointment_notes')->nullable()->after('appointment_status');
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->dropColumn([
                'embassy_center',
                'appointment_time',
                'appointment_status',
                'appointment_notes',
            ]);
        });
    }
};
