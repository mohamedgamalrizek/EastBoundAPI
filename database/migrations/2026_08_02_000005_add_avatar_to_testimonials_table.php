<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public review cards show a traveler photo. Nullable — the frontend falls
 * back to a generated initials avatar when it is empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('role');
            $table->string('city')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'city']);
        });
    }
};
