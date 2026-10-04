<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `galleries` only stored an `image_label` (a caption), so the public gallery
 * page had to hardcode its photos. `image` holds the real URL / upload path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('image')->nullable()->after('image_label');
            $table->integer('sort_order')->default(0)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['image', 'sort_order']);
        });
    }
};
