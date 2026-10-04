<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hotels are an ERP inventory record today. These columns let the same rows
 * power the public hotel-booking page (image + blurb + featured flag).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('image')->nullable()->after('category');
            $table->text('description')->nullable()->after('image');
            $table->boolean('is_featured')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['image', 'description', 'is_featured']);
        });
    }
};
