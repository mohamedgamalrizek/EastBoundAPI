<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hotel Allocation and Flight Allocation were two views of the same pilgrim
     * table with nowhere to record an allocation — no hotel column, no flight
     * column. Makkah and Madinah are separate stays, so each gets its own.
     */
    public function up(): void
    {
        Schema::table('hajj_pilgrims', function (Blueprint $table) {
            $table->string('makkah_hotel')->nullable()->after('group_name');
            $table->string('madinah_hotel')->nullable()->after('makkah_hotel');
            $table->string('room_no')->nullable()->after('madinah_hotel');

            $table->string('flight_no')->nullable()->after('room_no');
            $table->date('departure_date')->nullable()->after('flight_no');
            $table->date('return_date')->nullable()->after('departure_date');
            $table->string('seat_no')->nullable()->after('return_date');

            // Pilgrims pay in instalments over months; a single status flag
            // couldn't answer "how much is still outstanding?".
            $table->decimal('amount_paid', 12, 2)->default(0)->after('seat_no');
            $table->decimal('amount_due', 12, 2)->default(0)->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('hajj_pilgrims', function (Blueprint $table) {
            $table->dropColumn([
                'makkah_hotel', 'madinah_hotel', 'room_no',
                'flight_no', 'departure_date', 'return_date', 'seat_no',
                'amount_paid', 'amount_due',
            ]);
        });
    }
};
