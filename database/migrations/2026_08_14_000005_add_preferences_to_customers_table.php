<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portal users already keep their preferences in users.preferences (language,
 * currency, notification opt-ins) — but the mobile app authenticates as a
 * Customer, which had no such column, so its Settings screen could only save
 * to the phone. Same column, same keys, so both surfaces read one record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->json('preferences')->nullable()->comment('app prefs: language, currency, notification opt-ins');
        });
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('preferences'));
    }
};
