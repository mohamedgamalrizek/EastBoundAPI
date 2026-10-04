<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * customers.avatar was declared in the original create migration but never
     * reached live databases (the migration file was edited after it ran).
     * The API (ProfileController::updateAvatar) writes this column.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'avatar')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
