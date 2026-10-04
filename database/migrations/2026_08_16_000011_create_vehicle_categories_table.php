<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The vehicle categories a customer can request on the airport transfer /
 * car rental forms (Sedan, SUV…) and staff can pick when booking a transport
 * job. Used to be a hardcoded 4-option list on the public form and a free
 * text box on the admin one, so the two could say anything and never agreed
 * with each other.
 *
 * Seeded here rather than a demo-only seeder: unlike sample bookings or
 * leads, an empty list would leave both forms without a vehicle option in
 * every environment, demo or not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active'); // active | inactive
            $table->timestamps();
        });

        $now = now();

        DB::table('vehicle_categories')->insert([
            ['name' => 'Sedan',     'sort_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SUV',       'sort_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Microbus',  'sort_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coach',     'sort_order' => 4, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_categories');
    }
};
