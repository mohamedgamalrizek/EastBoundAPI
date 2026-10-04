<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `price` was already charged per traveller (bookings multiply it by head
     * count) but nothing said so, and a family booking had no way to pay a
     * child rate or a solo traveller a room supplement.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('packages', 'child_price')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->decimal('child_price', 12, 2)->nullable()->after('price');
            });
        }

        if (! Schema::hasColumn('packages', 'single_supplement')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->decimal('single_supplement', 12, 2)->nullable()->after('child_price');
            });
        }
    }

    public function down(): void
    {
        // These columns are part of the base packages table for fresh installs.
        // This migration only fills gaps on older databases.
    }
};
