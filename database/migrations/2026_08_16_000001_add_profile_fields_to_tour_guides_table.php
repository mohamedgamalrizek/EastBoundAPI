<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guides', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('name');
            $table->text('bio')->nullable()->after('experience_years');
            // A guide who logs into the mobile app to see their assignments.
            $table->foreignId('user_id')->nullable()->after('bio')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tour_guides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['photo', 'bio']);
        });
    }
};
