<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The flights a hajj batch actually travels on.
 *
 * `flight_routes` is the marketing fare list for the public site — it has no
 * flight numbers and no seats — so flight allocation had nothing to offer and
 * fell back to typing "BG-1011" and "12A" into free-text boxes on every row.
 * Nothing stopped a typo, and nothing stopped two pilgrims being given the
 * same seat.
 *
 * This table is the list the office picks from. The seat map is described
 * rather than stored row by row (rows × letters), because a seat only matters
 * once somebody is in it — and that is already on the pilgrim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hajj_flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_no', 30)->unique();
            $table->string('airline')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();

            // 30 rows × "ABCDEF" = seats 1A … 30F.
            $table->unsignedSmallInteger('seat_rows')->default(30);
            $table->string('seat_letters', 12)->default('ABCDEF');

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Flights already written on pilgrims become the first entries, with
        // whatever dates those pilgrims carry, so the screen keeps working.
        $existing = DB::table('hajj_pilgrims')
            ->whereNotNull('flight_no')
            ->where('flight_no', '!=', '')
            ->select('flight_no')
            ->selectRaw('MIN(departure_date) as departure_date')
            ->selectRaw('MIN(return_date) as return_date')
            ->groupBy('flight_no')
            ->get();

        foreach ($existing as $row) {
            DB::table('hajj_flights')->insert([
                'flight_no'      => $row->flight_no,
                'departure_date' => $row->departure_date,
                'return_date'    => $row->return_date,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hajj_flights');
    }
};
