<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'flight_bookings', 'hotel_bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'flight_bookings', 'hotel_bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('lead_id');
            });
        }
    }
};
